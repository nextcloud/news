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
     * @param id the id of the element
     */
    Menu = function(cls, id){
        this._class = cls;
        this._children = [];
        this._parent = false;
        this._id = id;
        this._$htmlElement = $('<ul>');
    }

    Menu.prototype.addChild = function(node){
        node.setParent(this);
        this._children.push(node);
    }

    Menu.prototype.setParent = function(node){
        this._parent = node;
    }
    
    /**
     * Recursively traverse the menu and returns the 
     * Node element matching the type and id
     * @return the node element
     */
    Menu.prototype.findNode = function(id, type){
        for(var i=0; i<this._children.length; i++){
            var child = this._children[i];
            if(child._type === type && child._id === id){
                return child;
            } else {
                return child.findNode(type, id);
            }
        }
    }

    /**
     * Recursively remove all occurences of the node from the dom and
     * from the datastructure
     */
    Menu.prototype.removeNode = function(id, type){
        var nodeIndex;
        for(var i=0; i<this._children.length; i++){
            var child = this._children[i];
            if(child._type === type && child._id === id){
                var nodeIndex = i;
                // if we have children, we need to remove their 
                // html from the dom first then we need to 
                this._$htmlElement.remove(child);
                this._children.splice(nodeIndex, 1);
            } else {
                child.removeNode(type, id);
            }
        }
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
            var node = new MenuNode(nodeInfo.title, nodeInfo.id, 
                nodeInfo.type, nodeInfo.icon, nodeInfo.unreadCount);
            attachToNode.addChild(node);
            this.populateFromJSON(nodeInfo.children, node);
        }
    }

    /**
     * Sets the unreadcount for all items from json markup
     * format of json is: 
     [ 
          { 
            id: '1',
            type: 1,
            unreadCount: 39
          }, 
          {
            ...
          }
     ]
     */
    Menu.prototype.setUnreadCountFromJSON = function(json){
        for(var i=0; i<json.length; i++){
            var element = json[i];
            var node = this.findNode(element.id, element.type);
            node.setUnreadCount(element.unreadCount);
        }
    }


    Menu.prototype.render = function(){
        var $html = this._$htmlElement.addClass(this._class).data('id', this._id);
        for(var i=0; i<this._children.length; i++){
            var child = this._children[i];
            var childHTML = child.render();
            $html.append(childHTML);
        }
        return $html;
    }

    News.Menu = Menu;


    /**
     * Items which are in the menu
     * @param title the caption of the menu item
     * @param type the type of the node, a MenuNodeType
     * @param id the id of the node. id and type must be unique!
     * @param icon is the little image that appears left of the caption
     * @param unreadCount the current count of unread items
     */
    MenuNode = function(title, id, type, icon, unreadCount){
        this._type = type;
        this._id = id;
        this._title = title;
        this._$htmlElement = $('<li>');
        this._children = [];
        this.setUnreadCount(unreadCount);
        this.setIcon(icon);
    }

    MenuNode.prototype = Object.create(Menu.prototype);

    /**
     * Sets the unread count and handles the appropriate css
     * classes
     */
    MenuNode.prototype.setUnreadCount = function(unreadCount){
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

    MenuNode.prototype.increaseUnreadCount = function(by){
        this._unreadCount += parseInt(by);
    }

    MenuNode.prototype.decreaseUnreadCount = function(by){
        this._unreadCount -= parseInt(by);
    }

    MenuNode.prototype.changeTitle = function(title){
        this._title = title;
        this._$htmlElement.children('.title').html(this._title);
    }

    MenuNode.prototype.setIcon = function(icon){
        if(icon !== undefined){
            this._icon = icon;
            this._$htmlElement.css('background-image', this._icon);
        }
    }

    /**
     * Toggles the selection class
     */
    MenuNode.prototype.toggleSelected = function(){
        this._$htmlElement.toggle('selected');
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
        
        var $title = $('<span>').addClass('title').html(this._title);
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
    
    News.MenuNode = MenuNode; 

})();