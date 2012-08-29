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
 */

var News = News || {};

(function(){

    /**
     * Enumeration for menu items
     */
    MenuNodeType = {
        'Feed': 0,
        'Folder': 1,
        'Filter': 2 // used for starred items or new items
    }

    News.MenuNodeType = MenuNodeType; 


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
     * @param type the type of the node
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
     * Updates a node in the menu
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


    // private

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
     * Items which are in the menu
     * @param type the type of the node, a MenuNodeType
     * @param id the id of the node. id and type must be unique!
     * @param data the data array containing title, icon and unreadCount
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
     * @param data the array with the data, if parts are undefined, theyre not 
     * updated
     */
    MenuNode.prototype.update = function(data){
        if(data.title !== undefined){
            this._title = data.title;
            this._$htmlElement.children('.title').html(this._title);
        }

        if(data.icon !== undefined){
            this._icon = data.icon;
            this._$htmlElement.css('background-image', this._icon);
        }

        if(data.unreadCount !== undefined){
            this._setUnreadCount(data.unreadCount);
        }
    }

    MenuNode.prototype.render = function(){
        var $elem = this._$htmlElement;

        // set the type class
        switch(this._type){
            case MenuNodeType.Feed:
                $elem.addClass('feed');
                break;

            case MenuNodeType.Folder:
                $elem.addClass('folder');
                break;

            case MenuNodeType.Filter:
                $elem.addClass('filter');
                break;

            default:
                break;
        }
        
        var $title = $('<a>').addClass('title').html(this._title).attr('href', '#');
        $elem.append($title);
        
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

    // private

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
            this._$htmlElement.removeClass('all_read');  
        }

        this._unreadCount = unreadCount;
    }

    /**
     * Toggles the selection class
     */
    MenuNode.prototype._toggleSelected = function(){
        this._$htmlElement.toggle('selected');
    }

})();