/**
* ownCloud - News app
*
* @author Bernhard Posselt
* Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
*
* This file is licensed under the Affero General Public License version 3 or later.
* See the COPYING-README file
*
*/

/**
 * This file includes objects for creating and accessing the feed menu
 * BEWARE: Recursion ahead!
 */

var News = News || {};
var t = t || function(app, string){ return string; }; // mock translation for local testing

(function(){

    /*##########################################################################
     * MenuNodeType
     *#########################################################################/
    /**
     * Enumeration for menu items
     */
    MenuNodeType = {
        'Feed': 0,
        'Folder': 1,
        'Starred': 2,
        'New': 3
    }

    // TODO: set paths for icons
    MenuNodeTypeDefaultIcon = {
        'Feed': '',
        'Folder': '',
        'Starred': '',
        'New': ''
    }

    News.MenuNodeType = MenuNodeType; 


    /*##########################################################################
     * Menu
     *#########################################################################/
    /**
     * This is the basic menu used to construct and maintain the menu
     * @param cls the css class of the element
     */
    Menu = function(cls){
        this._class = cls;
        this._children = [];
        this._parent = false;
        this._rendered = false;
        this._id = 0;
        this._$htmlElement = $('<ul>');
        this._selectedNode = undefined;
        this._showAll = false;
    }

    News.Menu = Menu;

    /**
     * Attaches a MenuNode to a node and renders it in the dom
     * @param parentType the type of the parent node
     * @param parentId the id of the parent node, if 0 the top menu is used
     * @param node the MenuNode that should be created
     */
    Menu.prototype.createNode = function(parentType, parentId, node){
        // if we pass the parentId 0 we assume the parent is the menu
        var parentNode;
        if(parentId === 0){
            parentNode = this;
        } else {
            parentNode = this._findNode(parentType, parentId);
        }
        parentNode._addChildNode(node);
        parentNode._$htmlElement.append(node.render());
    }

    /**
     * Recursively remove all occurences of the node from the dom and
     * from the datastructure
     * @param type the type of the node (MenuNodeType)
     * @param id the id of the node
     * @return the childelemnt or undefined if not found
     */
    Menu.prototype.removeNode = function(type, id){
        var nodeIndex;
        for(var i=0; i<this._children.length; i++){
            var child = this._children[i];
            if(child._type === type && child._id === id){
                var nodeIndex = i;
                // if we have children, we need to remove their 
                // html from the dom first then we need to 
                child._$htmlElement.remove();
                this._children.splice(nodeIndex, 1);
                return child;
            } else {
                var child = child.removeNode(type, id);
                if(child !== undefined){
                    return child;
                }
            }
        }
        return undefined;
    }

    /**
     * Updates a node in the menu and returns it
     * @return the updated node
     */
    Menu.prototype.updateNode = function(type, id, data){
        var node = this._findNode(type, id);
        node.update(data);
    }

    /**
     * Creates the menu from a json structure
     * @param json the json looks like this
        [
            {
                id: 1,,
                title: 'hi',
                type: MenuNodeType.Folder,
                icon: 'url/to/jpg.png',        
                children: [
                    {
                        id: 1,
                        title: 'hi too',
                        type: MenuNodeType.Feed,  
                        children: []                   
                    },
                    {
                        ...
                    }
                ]
            }, 
            {
                ...
            }
        ]
     * @param attachToNode used for recursion. to set the current
     * element to the node, pass the current structure
     */
    Menu.prototype.populateFromJSON = function(json, attachToNode){
        for(var i=0; i<json.length; i++){
            var nodeInfo = json[i];
            var nodeData = {
                title: nodeInfo.title,
                icon: nodeInfo.icon,
                unreadCount: nodeInfo.unreadCount
            };
            var node = new MenuNode(nodeInfo.type, nodeInfo.id, nodeData);
            attachToNode._addChildNode(node);
            this.populateFromJSON(nodeInfo.children, node);
        }
    }

    Menu.prototype.render = function(){
        var $html = this._$htmlElement.addClass(this._class).data('id', this._id);
        for(var i=0; i<this._children.length; i++){
            var child = this._children[i];
            var childHTML = child.render();
            $html.append(childHTML);
        }
        this._rendered = true;

        return $html;
    }


    /**
     * Returns the number of elements in the menu
     * @return the number of all children
     */
    Menu.prototype.getSize = function(){
        var size = this._children.length;
        for(var i=0; i<this._children.length; i++){
            size += this._children[i].getSize();
        }
        return size;
    }

    /**
     * Shortcut for intially setting the selected node
     * @param type the type of the node (MenuNodeType)
     * @param id the id of the node
     */
    Menu.prototype.setSelected = function(type, id){
        this._setSelected(this._findNode(type, id));
    }

    /**
     * Elements should only be set as hidden if the user clicked on a new entry
     * Then all all_read entries should be marked as hidden
     * This function is used to hide all the read ones if showAll is false,
     * otherwise shows all
     */
    Menu.prototype.triggerHideRead = function(){
        // only trigger in the root menu
        if(this._parent === false){
            if(this._showAll){
                $(this._$htmlElement).find('.hidden').each(function(){
                    $(this).removeClass('hidden');
                });
            } else {
                $(this._$htmlElement).find('.all_read').each(function(){
                    if(!$(this).hasClass('hidden')){
                        $(this).addClass('hidden');
                    }
                });                
            }
            
        }
    }

    /**
     * Sets the showAll value
     * @param showAll if true, all read folders and feeds are being shown
     * if false only unread ones are shown
     */
    Menu.prototype.setShowAll = function(showAll){
        this._showAll = showAll;
        this.triggerHideRead();
    }

    /**
     * Shortcut for toggling show all
     */
    Menu.prototype.toggleShowAll = function(){
        this.setShowAll(!this._showAll);
    }


    /* #### private #### */

    /**
     * Adds a node to the current one
     * @param node the node which we want to add to the menu
     */
    Menu.prototype._addChildNode = function(node){
        node._parent = this;
        this._children.push(node);
    }
    
    /**
     * Recursively traverse the menu and returns the 
     * Node element matching the type and id
     * @param type the type of the node (MenuNodeType)
     * @param id the id of the node
     * @return the node element or undefined
     */
    Menu.prototype._findNode = function(type, id){
        for(var i=0; i<this._children.length; i++){
            var child = this._children[i];
            if(child._type === type && child._id === id){
                return child;
            } else {
                var childNode = child._findNode(type, id);
                if(childNode !== undefined){
                    return childNode;
                }
            }
        }
        return undefined;
    }


    /**
     * Sets a node selected and removes the class from the previous node
     * @param node the node which should be set as selected
     */
    Menu.prototype._setSelected = function(node){
        if(this._selectedNode !== undefined){
            this._selectedNode._$htmlElement.removeClass('selected');
        }
        node._$htmlElement.addClass('selected');
        this._selectedNode = this;
    }


    /*##########################################################################
     * MenuNode
     *#########################################################################/
    /**
     * Items which are in the menu
     * @param type the type of the node (MenuNodeType)
     * @param id the id of the node. id and type must be unique!
     * @param data the data array like {title: 'title', unreadCount: 1, icon: 'path/icon.png'}
     */
    MenuNode = function(type, id, data){
        this._type = type;
        this._id = id;
        this._$htmlElement = $('<li>');
        this._children = [];
        this.update(data);
    }

    MenuNode.prototype = Object.create(Menu.prototype);
    News.MenuNode = MenuNode;

    /**
     * Updates the given values of a node
     * @param data the data array like {title: 'title', unreadCount: 1, icon: 'path/icon.png'}
     */
    MenuNode.prototype.update = function(data){
        if(data.title !== undefined){
            this._title = data.title;
            this._$htmlElement.children('.title').html(data.title);
        }

        if(data.icon !== undefined){
            this._icon = data.icon;
            var iconCss = 'url("' + data.icon + '")';
            this._$htmlElement.css('background-image', iconCss);
        } else {
            // if undefined, we check for default icons
            this._icon = MenuNodeTypeDefaultIcon[this._type];
        }

        if(data.unreadCount !== undefined){
            this._setUnreadCount(data.unreadCount);
        }
    }

    /**
     * This function creates the html of the node and its children
     * @return the html of the node and its children
     */
    MenuNode.prototype.render = function(){
        var self = this;
        var $elem = this._$htmlElement;

        var $title = $('<a>').addClass('title').html(this._title).attr('href', '#');
        $title.attr('title', t('news', 'Load feed'));
        $title.click(function(){
            self._click();
        });

        // buttons
        var $deleteButton = $('<button>').addClass('svg action feeds_delete');
        $deleteButton.attr('title', t('news', 'Delete'));
        $deleteButton.click(function(){
            self._deleteClick();
        });

        var $expandButton = $('<button>').addClass('action collapsable');
        $expandButton.attr('title', t('news', 'Expand/Collapse'));
        $expandButton.click(function(){
            self._expandClick();
        });

        var $editButton = $('<button>').addClass('svg action feeds_edit');
        $editButton.attr('title', t('news', 'Edit'));
        $editButton.click(function(){
            self._editClick();
        });

        // set the type class
        switch(this._type){
            case MenuNodeType.Feed:
                $elem.append($title);
                $elem.append($deleteButton);
                $elem.addClass('feed');
                break;

            case MenuNodeType.Folder:
                $elem.append($expandButton);
                $elem.append($title);
                $elem.append($editButton);
                $elem.append($deleteButton);
                $elem.addClass('folder');
                break;

            case MenuNodeType.Starred:
                $elem.append($title);
                $elem.addClass('filter');
                break;

            default:
                break;
        }

        // recursively append children
        var $subNode = $('<ul>');
        for(var i=0; i<this._children.length; i++){
            var node = this._children[i];
            $subNode.append(node.render());
        }
        if(this._children.length > 0){
            $elem.append($subNode);   
        }
        
        return $elem;
    }

    /* #### private #### */

    /**
     * Handles every important change that has to be made if the link was
     * clicked
     */
    MenuNode.prototype._click = function(){
        this._setSelected(this);
        // TODO: load new items
    }

    /**
     * Handles every important change that has to be made if the delete button was
     * clicked
     */
    MenuNode.prototype._deleteClick = function(){
        // TODO: send delete event
    }

    /**
     * Handles every important change that has to be made if the edit button was
     * clicked
     */
    MenuNode.prototype._editClick = function(){
        // TODO: show edit window
    }

    /**
     * Handles every important change that has to be made if the expand button
     * was clicked
     */
    MenuNode.prototype._expandClick = function(){
        this._$htmlElement.children('.collapsable').toggleClass('triggered');
        var $sublist = this._$htmlElement.children('ul');
        if($sublist.length > 0){
            $sublist.toggle();
        }
    }

    /**
     * Sets the unread count and handles the appropriate css
     * classes
     */
    MenuNode.prototype._setUnreadCount = function(unreadCount){
        unreadCount = parseInt(unreadCount);

        if(unreadCount === 0){
            this._$htmlElement.addClass('all_read');
        } 

        if(this._unreadCount !== undefined && this._unreadCount === 0
            && unreadCount > 0){
            this._$htmlElement.removeClass('all_read hidden');  
        }

        this._unreadCount = unreadCount;
    }


})();