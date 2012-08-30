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

/**
 * HOWTO
 *

We create a new instance of the menu. Then we need to bind it on an ul which contains
all the items:

    var menu = new News.Menu();
    menu.bindOn('#feeds ul');

Updating nodes (you dont have to set all values in data):

    var nodeData = {
        icon: 'some/icon.png',
        unreadCount: 4,
        title: 'The verge'
    }
    var nodeType = News.MenuNodeType.Feed;
    var nodeId = 2;
    menu.updateNode(nodeType, nodeId, nodeData);


Deleting nodes:

    var id = 2;
    var removeDom = true;
    var type = News.MenuNodeType.Feed;
    var removedObject = menu.removeNode(type, id, removeDom);


Creating nodes:
    
    var nodeType = News.MenuNodeType.Feed;
    var nodeId = 6;
    var nodeData = {
        icon: 'some/icon.png',
        unreadCount: 4,
        title: 'The verge'
    }
    var node = new News.MenuNode(nodeType, nodeId, nodeData);

    var parentType = News.MenuNodeType.Folder;
    var parentId = 0;
    menu.createNode(parentType, parentId, node);


If you want to show all feeds, also feeds which contain only read items, use

    menu.setShowAll(true);

If you want to hide feeds and folders with only read items, use

    menu.setShowAll(false);

The default value is false. If you want to toggle this behaviour, theres a shortcut

    menu.toggleShowAll();


To hide all articles with read feeds, the setShowAll has to be set to false. The 
hiding is only triggered after a new feed/folder was being loaded. If you wish to
trigger this manually, use:

    menu.triggerHideRead();

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
        'Subscriptions': 3
    }

    // map css classes to MenuNodeTypes
    MenuNodeTypeClass = {};
    MenuNodeTypeClass[MenuNodeType.Feed] = 'feed';
    MenuNodeTypeClass[MenuNodeType.Folder] = 'folder';
    MenuNodeTypeClass[MenuNodeType.Starred] = 'starred';
    MenuNodeTypeClass[MenuNodeType.Subscriptions] = 'subscriptions';

    News.MenuNodeType = MenuNodeType; 


    /*##########################################################################
     * Menu
     *#########################################################################/
    /**
     * This is the basic menu used to construct and maintain the menu
     * @param showAll if all items should be shown by default
     */
    Menu = function(showAll){
        this._showAll = showAll;
        this._unreadCount = {
            Feed: {},
            Folder: {},
            Starred: 0,
            Subscriptions: 0
        };
    }

    News.Menu = Menu;

    /**
     * 
     */
    Menu.prototype.removeNode = function(type, id){

    }

    /**
     * A node can only be added to a folder or to the root
     */
    Menu.prototype.addNode = function(parentId, type, id, data){

    }

    /**
     * 
     */
    Menu.prototype.updateNode = function(type, id, data){

    }

    /**
     * Binds the menu on an existing menu
     * @param css Selector the selector to get the element with jquery
     */
    Menu.prototype.bindOn = function(cssSelector){
        var self = this;
        this._$root = $(cssSelector);
        this._id = this._$root.data('id');
        this._$root.children('li').each(function(){
            self._bindMenuItem($(this));
        });
        this._bindDroppable(this._$root);
    }

    /**
     * Binds the according handlers and reads in the meta data for each node
     * @param $listItem the jquery list element
     */
    Menu.prototype._bindMenuItem = function($listItem){        
        switch(this._listItemToMenuNodeType($listItem)){
            case MenuNodeType.Feed:
                this._bindFeed($listItem);
                break;
            case MenuNodeType.Folder:
                this._bindFolder($listItem);
                break;
            case MenuNodeType.Starred:
                this._bindStarred($listItem);
                break;
            case MenuNodeType.Subscriptions:
                this._bindSubscriptions($listItem);
                break;
            default:
                console.log('Found unknown MenuNodeType');
                console.log($listItem);
                break;
        }
    }

    /**
     * Returns the MenuNodeType of a list item
     * @param $listItem the jquery list element
     * @return the MenuNodeType of the jquery element
     */
    Menu.prototype._listItemToMenuNodeType = function($listItem){
        if($listItem.hasClass(this._menuNodeTypeToClass(MenuNodeType.Feed))){
            return MenuNodeType.Feed;
        } else if($listItem.hasClass(this._menuNodeTypeToClass(MenuNodeType.Folder))){
            return MenuNodeType.Folder;
        } else if($listItem.hasClass(this._menuNodeTypeToClass(MenuNodeType.Starred))){
            return MenuNodeType.Starred;
        } else if($listItem.hasClass(this._menuNodeTypeToClass(MenuNodeType.Subscriptions))){
            return MenuNodeType.Subscriptions;
        }
    }

    /**
     * Returns the classname of the MenuNodeType
     * @param menuNodeType the type of the menu node
     * @return the class of the MenuNodeType
     */
    Menu.prototype._menuNodeTypeToClass = function(menuNodeType){
        return MenuNodeTypeClass[menuNodeType];
    }

    /**
     * Binds event listeners to the folder and its subcontents
     * @param $listItem the jquery list element
     */
    Menu.prototype._bindFolder = function($listItem){
        var self = this;
        var id = $listItem.data('id');
        this._setUnreadCount(MenuNodeType.Folder, id, 
            this._getAndRemoveUnreadCount($listItem));

        this._resetOpenFolders();

        // bind subitems
        $children.each(function(){
            self._bindMenuItem($(this));
        });

        // bind click listeners
        this._bindDroppable($listItem);
        this._bindDroppable($listItem.children('ul'));

        $listItem.children('.title').click(function(){
            self._load(MenuNodeType.Folder, id);
            return false;
        });

        $listItem.children('.collapsable_trigger').click(function(){
            self._toggleCollapse($listItem);
        });

        $listItem.children('.feeds_delete').click(function(){
            self._delete(MenuNodeType.Folder, id);
        });

        $listItem.children('.feeds_edit').click(function(){
            self._edit(MenuNodeType.Folder, id);
        });

        $listItem.children('.feeds_markread').click(function(){
            self._markRead(MenuNodeType.Folder, id);
        });
    }

    Menu.prototype._bindFeed = function($listItem){
        var self = this;
        var id = $listItem.data('id');
        this._setUnreadCount(MenuNodeType.Feed, id, 
            this._getAndRemoveUnreadCount($listItem));

        // bind click listeners
        $listItem.children('.title').click(function(){
            self._load(MenuNodeType.Folder, id);
            return false;
        });

        $listItem.children('.feeds_delete').click(function(){
            self._delete(MenuNodeType.Folder, id);
        });

        $listItem.children('.feeds_markread').click(function(){
            self._markRead(MenuNodeType.Folder, id);
        });

        // bind draggable
        $listItem.draggable({ 
            revert: true,
            stack: '> li',   
            zIndex: 1000,
            axis: 'y',
        });
    }

    Menu.prototype._bindStarred = function($listItem){
        this._setUnreadCount(MenuNodeType.Starred, 0, 
            this._getAndRemoveUnreadCount($listItem));

        // bind click listeners
        $listItem.children('.title').click(function(){
            self._load(MenuNodeType.Folder, id);
            return false;
        });
    }

    Menu.prototype._bindSubscriptions = function($listItem){
        this._setUnreadCount(MenuNodeType.Subscriptions, 0, 
            this._getAndRemoveUnreadCount($listItem));

        // bind click listeners
        $listItem.children('.title').click(function(){
            self._load(MenuNodeType.Folder, id);
            return false;
        });
    }

    Menu.prototype._resetOpenFolders = function(){
        $folders = $('.folder');
        $folders.each(function(){
            $children = $(this).children('ul').children('li');
            if($children.length > 0){
                $(this).addClass('collapsable');
            } else {
                $(this).removeClass('collapsable');
            }
        });
    }

    Menu.prototype._load = function(type, id){

    }

    Menu.prototype._toggleCollapse = function($listItem){
        $listItem.toggleClass('open');
        $listItem.children('.collapsable_trigger').toggleClass('triggered');
        $listItem.children('ul').toggle();
    }

    Menu.prototype._getAndRemoveUnreadCount = function($listItem){
        var $unreadCounter = $listItem.children('.unread_items_counter');
        var unreadCount = parseInt($unreadCounter.html());
        $unreadCounter.remove();
        return unreadCount;
    }

    /**
     * Sets the unread count and handles the appropriate css
     * classes
     * @param unreadCount the count of unread items
     */
    Menu.prototype._setUnreadCount = function(type, id, unreadCount){
        /**
        if(unreadCount === 0){
            this._$htmlElement.addClass('all_read');
        } 

        if(this._unreadCount !== undefined && this._unreadCount === 0
            && unreadCount > 0){
            this._$htmlElement.removeClass('all_read hidden');  
        }

        this._unreadCount = unreadCount;
        */
        //this._unreadCount[type] 
    }

    /**
     * Binds a droppable on the element
     * @param $elem the element that should be set droppable
     */
    Menu.prototype._bindDroppable = function($elem){
        var self = this;
        $elem.droppable({
            accept: '.feed',
            hoverClass: 'dnd_over',
            greedy: true,
            drop: function(event, ui){
                var $dropped = $(this);
                var $dragged = $(ui.draggable);

                var feedId = parseInt($dragged.data('id'));
                var folderId = parseInt($dropped.data('id'));

                // to also be able to drop this on a folder entry or the top menu
                // we have to check if we use a folder and append to a different
                // item
                if($dropped.hasClass(self._menuNodeTypeToClass(MenuNodeType.Folder))){
                    $dropped.children('ul').append($dragged[0]);
                } else {
                    $dropped.append($dragged[0]);
                }

                self._resetOpenFolders();
                self._moveItemToFolder(feedId, folderId);
            }
        });
    }


    Menu.prototype._moveItemToFolder = function(feedId, folderId){
        // TODO
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


})();