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
 * This file includes objects for binding and accessing the feed menu
 */

/**
 * HOWTO
 *

We create a new instance of the menu. Then we need to bind it on an ul which contains
all the items:

    var updateIntervalMiliseconds = 2000;
    var items = new News.Items('#feed_items');
    var menu = new News.Menu(updateIntervalMiliseconds, items);
    menu.bindOn('#feeds ul');

Updating nodes (you dont have to set all values in data):

    var nodeType = News.MenuNodeType.Feed;
    var nodeId = 2;
    var nodeData = {
        unreadCount: 4,
        title: 'The verge'
    }
    menu.updateNode(nodeType, nodeId, nodeData);


Deleting nodes:

    var id = 2;
    var type = News.MenuNodeType.Feed;
    var removedObject = menu.removeNode(type, id);


Creating nodes:

    var parentId = 0;
    var html = '<nodehtml>';
    menu.addNode(parentId, html);


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

If you want to load a feed or folder directly, use

    var id = 2;
    var type = News.MenuNodeType.Folder;
    menu.load(type, id);

*/

var News = News || {};

(function(){

    /*##########################################################################
     * MenuNodeType
     *########################################################################*/
    /**
     * Enumeration for menu items
     */
    MenuNodeType = {
        'Feed': 0,
        'Folder': 1,
        'Starred': 2,
        'Subscriptions': 3
    };

    // map css classes to MenuNodeTypes
    MenuNodeTypeClass = {};
    MenuNodeTypeClass[MenuNodeType.Feed] = 'feed';
    MenuNodeTypeClass[MenuNodeType.Folder] = 'folder';
    MenuNodeTypeClass[MenuNodeType.Starred] = 'starred';
    MenuNodeTypeClass[MenuNodeType.Subscriptions] = 'subscriptions';

    News.MenuNodeType = MenuNodeType;


    /*##########################################################################
     * Menu
     *########################################################################*/
    /**
     * This is the basic menu used to construct and maintain the menu
     * @param updateIntervalMiliseconds how often the menu should refresh
     * @param items the items object
     */
    Menu = function(updateIntervalMiliseconds, items){
        var self = this;
        this._updatingCount = 0;
        this._updateInterval = updateIntervalMiliseconds;
        this._items = items;
        this._showAll = $('#view').hasClass('show_all');

        this._unreadCount = {
            Feed: {},
            Folder: {},
            Starred: 0,
            Subscriptions: 0
        };
    };

    News.Menu = Menu;

    /**
     * Adds a node to the menu. A node can only be added to a folder or to the root
     * @param parentId the id of the parent folder, 0 for root
     * @param html the html to add
     */
    Menu.prototype.addNode = function(parentId, html){
        parentId = parseInt(parentId, 10);
        var $parentNode;
        var $html = $(html);

        if(parentId === 0){
            $parentNode = this._$root;
        } else {
            $parentNode = this._getNodeFromTypeAndId(MenuNodeType.Folder, parentId).children('ul');
            // every folder we add to should be opened again
            $parentNode.parent().addClass('open');
            $parentNode.show();
            $parentNode.siblings('.collapsable_trigger').removeClass('triggered');
        }

        switch(this._getIdAndTypeFromNode($html).type){
            case MenuNodeType.Feed:
                this._bindFeed($html);
                break;
            case MenuNodeType.Folder:
                this._bindFolder($html);
                break;
        }

        $parentNode.append($html);
        this._resetOpenFolders();
    };

    /**
     * Updates the title and/or unread count of a node
     * @param type the type (MenuNodeType)
     * @param id the id
     * @param data a json array with the data for the node {title: '', 'unreadCount': 3}
     */
    Menu.prototype.updateNode = function(type, id, data){
        var $node = this._getNodeFromTypeAndId(type, id);
        id = parseInt(id, 10);

        if(data.title !== undefined){
            // prevent xss
            var title = $('<div>').text(data.title).html();
            $node.children('.title').html(title);
        }

        if(data.undreadCount !== undefined){
            this._setUnreadCount(type, id, data.unreadCount);
        }
    };

    /**
     * Removes a node and its subnodes from the menu
     * @param type the type (MenuNodeType)
     * @param id the id
     */
    Menu.prototype.removeNode = function(type, id){
        id = parseInt(id, 10);
        var $node = this._getNodeFromTypeAndId(type, id);
        $node.remove();
    };

    /**
     * Elements should only be set as hidden if the user clicked on a new entry
     * Then all all_read entries should be marked as hidden
     * This function is used to hide all the read ones if showAll is false,
     * otherwise shows all
     */
    Menu.prototype.triggerHideRead = function(){
        if(this._showAll){
            $(this._$root).find('.hidden').each(function(){
                $(this).removeClass('hidden');
            });
        } else {
            $(this._$root).find('.all_read').each(function(){
                // dont hide folders with the currently selected feed
                // or the currently selected feed
                if(!$(this).hasClass('active') && $(this).find('.active').length === 0){
                    $(this).addClass('hidden');
                }
            });
        }
        this._resetOpenFolders();
    };

    /**
     * Marks the current feed as all read
     */
    Menu.prototype.markCurrentFeedRead = function(){
        this._markRead(this._activeFeedType, this._activeFeedType);
    };

    /**
     * Sets the showAll value
     * @param showAll if true, all read folders and feeds are being shown
     * if false only unread ones are shown
     */
    Menu.prototype.setShowAll = function(showAll){
        this._showAll = showAll;
        this.triggerHideRead();
        // needed because we have items that are older
        // but not yet cached. We cache by remembering the newest item id
        this._items.emptyItemCache();
        this.load(this._activeFeedType, this._activeFeedId);
    };

    /**
     * Returns the value of show all
     * @return true if show all
     */
    Menu.prototype.isShowAll = function() {
        return this._showAll;
    };

    /**
     * Shortcut for toggling show all
     */
    Menu.prototype.toggleShowAll = function(){
        this.setShowAll(!this._showAll);
    };

    /**
     * Loads a new feed into the right content
     * @param type the type (MenuNodeType)
     * @param id the id
     */
    Menu.prototype.load = function(type, id){
        var self = this;
        self._setActiveFeed(type, id);

        this._items.load(type, id, function(){
            self.triggerHideRead();
        });
    };

    /**
     * Returns the ids of all feeds from a folder
     * @param folderId the id of the folder
     * @return an array with all the feed ids
     */
    Menu.prototype.getFeedIdsOfFolder = function(folderId) {
        $folder = this._getNodeFromTypeAndId(MenuNodeType.Folder, folderId);
        var ids = [];
        $folder.children('ul').children('li').each(function(){
            ids.push(parseInt($(this).data('id'), 10));
        });
        return ids;
    };

    /**
     * Increments the unreadcount of a folder by 1
     * @param type the type (MenuNodeType)
     * @param id the id
     */
    Menu.prototype.incrementUnreadCount = function(type, id) {
        var unreadCount;
        switch(type){
            case MenuNodeType.Feed:
                unreadCount = this._unreadCount.Feed[id];
                break;
            case MenuNodeType.Starred:
                unreadCount = this._unreadCount.Starred;
                break;
            default:
                console.log('Can only set unreadcount of starred items or feeds');
                break;
        }
        this._setUnreadCount(type, id, unreadCount+1);
    };

     /**
     * Decrements the unreadcount of a folder by 1
     * @param type the type (MenuNodeType)
     * @param id the id
     */
    Menu.prototype.decrementUnreadCount = function(type, id) {
        var unreadCount;
        switch(type){
            case MenuNodeType.Feed:
                unreadCount = this._unreadCount.Feed[id];
                break;
            case MenuNodeType.Starred:
                unreadCount = this._unreadCount.Starred;
                break;
            default:
                console.log('Can only set unreadcount of starred items or feeds');
                break;
        }
        this._setUnreadCount(type, id, unreadCount-1);
    };

    /**
     * Binds the menu on an existing menu
     * @param css Selector the selector to get the element with jquery
     */
    Menu.prototype.bindOn = function(cssSelector){
        var self = this;
        // bind menu
        this._$root = $(cssSelector);
        this._id = this._$root.data('id');
        this._$root.children('li').each(function(){
            self._bindMenuItem($(this));
        });
        this._bindDroppable(this._$root);
        this._$activeFeed = $('#feeds .active');
        this._activeFeedId = this._$activeFeed.data('id');
        this._activeFeedType = this._listItemToMenuNodeType(this._$activeFeed);

        setTimeout(function(){
            self._updateUnreadCountAll();
        }, 3000);

        setInterval(function(){
            self._updateUnreadCountAll();
        }, self._updateInterval);

        this.triggerHideRead();
    };

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
    };

    /**
     * Binds event listeners to the folder and its subcontents
     * @param $listItem the jquery list element
     */
    Menu.prototype._bindFolder = function($listItem){
        var self = this;
        var id = $listItem.data('id');
        var $children = $listItem.children('ul').children('li');

        this._resetOpenFolders();

        // bind subitems
        $children.each(function(){
            self._bindMenuItem($(this));
        });

        // bind click listeners
        this._bindDroppable($listItem);
        this._bindDroppable($listItem.children('ul'));

        $listItem.children('.title').click(function(){
            self.load(MenuNodeType.Folder, id);
            return false;
        });

        $listItem.children('.collapsable_trigger').click(function(){
            self._toggleCollapse($listItem);
        });

        $listItem.children('.buttons').children('.feeds_delete').click(function(){
            self._delete(MenuNodeType.Folder, id);
        });

        $listItem.children('.buttons').children('.feeds_edit').click(function(){
            self._edit(MenuNodeType.Folder, id);
        });

        $listItem.children('.buttons').children('.feeds_markread').click(function(){
            self._markRead(MenuNodeType.Folder, id);
        });
    };

    /**
     * Binds the callbacks for a normal feed
     * @param $listItem the jquery list element
     */
    Menu.prototype._bindFeed = function($listItem){
        var self = this;
        var id = $listItem.data('id');
        this._setUnreadCount(MenuNodeType.Feed, id,
            this._getUnreadCount($listItem));

        $listItem.children('.title').click(function(){
            // prevent loading when dragging
            if($(this).hasClass('noclick')){
                $(this).removeClass('noclick');
            } else {
                self.load(MenuNodeType.Feed, id);
            }
            return false;
        });

        $listItem.children('.buttons').children('.feeds_delete').click(function(){
            self._delete(MenuNodeType.Feed, id);
        });

        $listItem.children('.buttons').children('.feeds_markread').click(function(){
            self._markRead(MenuNodeType.Feed, id);
        });

        $listItem.draggable({
            revert: true,
            stack: '> li',
            zIndex: 1000,
            axis: 'y',
            start: function(event, ui){
                $(this).children('.title').addClass('noclick');
            }
        });
    };

    /**
     * Binds the callbacks for the starred articles feed
     * @param $listItem the jquery list element
     */
    Menu.prototype._bindStarred = function($listItem){
        var self = this;
        this._setUnreadCount(MenuNodeType.Starred, 0,
            this._getUnreadCount($listItem));

        $listItem.children('.title').click(function(){
            self.load(MenuNodeType.Starred, -1);
            return false;
        });

    };

    /**
     * Binds the callbacks for the new articles feed
     * @param $listItem the jquery list element
     */
    Menu.prototype._bindSubscriptions = function($listItem){
        var self = this;
        $listItem.children('.title').click(function(){
            self.load(MenuNodeType.Subscriptions, -2);
            return false;
        });

        $listItem.children('.buttons').children('.feeds_markread').click(function(){
            self._markRead(MenuNodeType.Subscriptions, 0);
        });
    };

    /**
     * Deletes a feed
     * @param type the type (MenuNodeType)
     * @param id the id
     */
    Menu.prototype._delete = function(type, id){
        var self = this;
        var confirmMessage;
        var url;
        var data;

        switch(type){
            case MenuNodeType.Feed:
                confirmMessage = t('news', 'Are you sure you want to delete this feed?');
                url = 'deletefeed.php';
                data = {
                    feedid: id
                };
                break;

            case MenuNodeType.Folder:
                confirmMessage = t('news', 'Are you sure you want to delete this folder and all its feeds?');
                url = 'deletefolder.php';
                data = {
                    folderid: id
                };
                break;
        }

        OC.dialogs.confirm(confirmMessage, t('news', 'Warning'), function(answer) {
            if(answer == true) {
                $.post(OC.filePath('news', 'ajax', url), data,function(jsonData){
                    if(jsonData.status == 'success'){
                        self.removeNode(type, id);
                        // if we move the current feed or folder, reload page
                        if(type === self._activeFeedType && id === self._activeFeedId){
                            window.location.reload();
                        }
                        self._resetOpenFolders();
                    } else{
                        OC.dialogs.alert(jsondata.data.message, t('news', 'Error'));
                    }
                });
            }
        });
    };

    /**
     * Shows the edit window for a feed
     * @param type the type (MenuNodeType)
     * @param id the id
     */
    Menu.prototype._edit = function(type, id){
        var $node = this._getNodeFromTypeAndId(type, id);
        var name = $node.children('.title').html();
        $('#changefolder_dialog').find('input[type=text]').val(name);
        $('#changefolder_dialog').find('input[type=hidden]').val(id);
        $('#changefolder_dialog').dialog('open');
    };

    /**
     * Marks all items of a feed as read
     * @param type the type (MenuNodeType)
     * @param id the id
     */
    Menu.prototype._markRead = function(type, id){
        var self = this;
        // make sure only feeds get past
        switch(type){

            case MenuNodeType.Folder:
                var $folder = this._getNodeFromTypeAndId(type, id);
                $folder.children('ul').children('li').each(function(){
                    var childData = self._getIdAndTypeFromNode($(this));
                    self._markRead(childData.type, childData.id);
                });
                break;

            case MenuNodeType.Subscriptions:
                this._$root.children('li').each(function(){
                    var childData = self._getIdAndTypeFromNode($(this));
                    if(childData.type === MenuNodeType.Folder ||
                        childData.type === MenuNodeType.Feed){
                        self._markRead(childData.type, childData.id);
                    }
                });
                break;

            case MenuNodeType.Feed:
                var data = {
                    feedId: id,
                    mostRecentItemId: this._items.getMostRecentItemId(type, id)
                };

                self._items.markAllRead(type, id);

                $.post(OC.filePath('news', 'ajax', 'setallitemsread.php'), data, function(jsonData) {
                    if(jsonData.status == 'success'){
                        self._setUnreadCount(type, id, parseInt(jsonData.data.unreadCount, 10));
                    } else {
                        OC.dialogs.alert(jsonData.data.message, t('news', 'Error'));
                    }
                });

                break;
        }
    };

    /**
     * Requests an update for unreadCount for all feeds and folders
     */
    Menu.prototype._updateUnreadCountAll = function() {
        var self = this;
        // prevent to fast firing updates
        if(this._updatingCount === 0){
            $.post(OC.filePath('news', 'ajax', 'feedlist.php'),function(jsonData){
                if(jsonData.status == 'success'){
                    var feeds = jsonData.data;
                    for (var i = 0; i<feeds.length; i++) {
                        self._updateUnreadCount(feeds[i]['id'], feeds[i]['url'], feeds[i]['folderid']);
                    }
                } else {
                    OC.dialogs.alert(jsonData.data.message, t('news', 'Error'));
                }
            });
        }
    };

    /**
     * Request unreadCount for one feed
     * @param feedId the id of the feed
     * @param feedUrl the url of the feed that should be updated
     * @param folderId the folderId fo the folder the feed is in
     */
    Menu.prototype._updateUnreadCount = function(feedId, feedUrl, folderId) {
        this._updatingCount += 1;
        var self = this;
        var data = {
            'feedid':feedId,
            'feedurl':feedUrl,
            'folderid':folderId
        };
        $.post(OC.filePath('news', 'ajax', 'updatefeed.php'), data, function(jsonData){
            if(jsonData.data !== undefined){ // FIXME: temporary fix
                if(jsonData.status == 'success'){
                    var newUnreadCount = jsonData.data.unreadcount;
                    // FIXME: starred items should also be set
                    self._setUnreadCount(MenuNodeType.Feed, feedId, newUnreadCount);
                } else {
                    OC.dialogs.alert(jsonData.data.message, t('news', 'Error'));
                }
            }
            self._updatingCount -= 1;
        });
    };

    /**
     * Toggles the child ul of a listitem
     * @param $listItem the jquery list element
     */
    Menu.prototype._toggleCollapse = function($listItem){
        $listItem.toggleClass('open');

        var folderId = this._getIdAndTypeFromNode($listItem).id;
        var data = {
            'folderId': folderId,
            'opened': $listItem.hasClass('open')
        };

        $.post(OC.filePath('news', 'ajax', 'collapsefolder.php'), data, function(jsondata){
            if(jsondata.status != 'success'){
                OC.dialogs.alert(jsonData.data.message, t('news', 'Error'));
            }
        });
    };

    /**
     * Sets the active feed to a new feed or folder
     * @param type the type (MenuNodeType)
     * @param id the id
     */
    Menu.prototype._setActiveFeed = function(type, id){
        var $oldFeed = this._$activeFeed;
        var $newFeed = this._getNodeFromTypeAndId(type, id);
        $oldFeed.removeClass('active');
        $newFeed.addClass('active');
        this._$activeFeed = $newFeed;
        this._activeFeedId = id;
        this._activeFeedType = type;
    };


    /**
     * Used when iterating over the menu. The unread count is being extracted,
     * the dom element is removed and the count is being returned
     * @param $listItem the jquery list element
     * @return the count of unread items
     */
    Menu.prototype._getUnreadCount = function($listItem){
        var $unreadCounter = $listItem.children('.unread_items_counter');
        var unreadCount = parseInt($unreadCounter.html(), 10);
        return unreadCount;
    };

    /**
     * Returns the jquery element for a type and an id
     * @param type the type (MenuNodeType)
     * @param id the id
     * @return the jquery node
     */
    Menu.prototype._getNodeFromTypeAndId = function(type, id) {
        if(type === MenuNodeType.Starred || type === MenuNodeType.Subscriptions){
            return $('.' + this._menuNodeTypeToClass(type));
        } else if(id === 0){
            return this._$root;
        } else {
            return $('.' + this._menuNodeTypeToClass(type) + '[data-id="' + id + '"]');
        }
    };

    /**
     * Returns id and type from a listnode
     * @param $listItem the list item
     * @return a json array with the id and type for instance { id: 1, type: MenuNodeType.Feed}
     */
    Menu.prototype._getIdAndTypeFromNode = function($listItem) {
        return {
            id: parseInt($listItem.data('id'), 10),
            type: this._listItemToMenuNodeType($listItem)
        };
    };

    /**
     * Returns the MenuNodeType of a list item
     * @param $listItem the jquery list element
     * @return the MenuNodeType of the jquery element or -1 for invalid
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
        } else {
            return -1;
        }
    };

    /**
     * Returns the classname of the MenuNodeType
     * @param menuNodeType the type of the menu node
     * @return the class of the MenuNodeType
     */
    Menu.prototype._menuNodeTypeToClass = function(menuNodeType){
        return MenuNodeTypeClass[menuNodeType];
    };

    /**
     * When feeds are moved to different folders and in the beginning, we
     * have to check for folders with children and add the appropriate
     * collapsable classes to give access to the collapasable button
     */
    Menu.prototype._resetOpenFolders = function(){
        var $folders = $('.folder');
        $folders.each(function(){
            var $children;
            if(this._showAll){
                $children = $(this).children('ul').children('li');
            } else {
                $children = $(this).children('ul').children('li.feed:not(.hidden)');
            }
            if($children.length > 0){
                $(this).addClass('collapsable');
            } else {
                $(this).removeClass('collapsable');
            }
        });
    };

    /**
     * Sets the unread count and handles the appropriate css classes
     * @param type the type (MenuNodeType) (folder and subscriptions udpate automatically)
     * @param id the id
     * @param unreadCount the count of unread items
     */
    Menu.prototype._setUnreadCount = function(type, id, unreadCount){
        unreadCount = parseInt(unreadCount, 10);
        if(unreadCount < 0){
            unreadCount = 0;
        }

        var $node = this._getNodeFromTypeAndId(type, id);

        // store the new unreadcount for starred and feeds
        switch(type){
            case MenuNodeType.Feed:
                this._unreadCount.Feed[id] = unreadCount;
                break;

            case MenuNodeType.Starred:
                this._unreadCount.Starred = unreadCount;
                break;

            default:
                console.log('Invalid or unknown MenuNodeType');
                break;
        }

        // check if we got a parent folder and update its unread count
        if(type === MenuNodeType.Feed){
            var $folder = $node.parent().parent();
            var folderData = this._getIdAndTypeFromNode($folder);
            if(folderData.type === MenuNodeType.Folder){
                var folderUnreadCount = 0;
                var self = this;
                $folder.children('ul').children('li').each(function(){
                    var feedData = self._getIdAndTypeFromNode($(this));
                    if(feedData.type === MenuNodeType.Feed){
                        folderUnreadCount += self._unreadCount.Feed[feedData.id];
                    }
                });
                this._applyUnreadCountStyle(MenuNodeType.Folder, folderData.id,
                    folderUnreadCount);
            }
        }

        // update subscriptions
        var subscriptionsUnreadCount = 0;
        $.each(this._unreadCount.Feed, function(key, value){
            subscriptionsUnreadCount += value;
        });
        this._unreadCount.Subscriptions = subscriptionsUnreadCount;
        this._applyUnreadCountStyle(MenuNodeType.Subscriptions, 0,
            subscriptionsUnreadCount);

        // lastly apply the new style to the feed
        this._applyUnreadCountStyle(type, id, unreadCount);
    };

    /**
     * Apply a style on a listitem based on its previous unreadcount and new
     * unreadcount
     * @param type the type (MenuNodeType)
     * @param id the id
     * @param unreadCount the new count of unread items
     */
    Menu.prototype._applyUnreadCountStyle = function(type, id, unreadCount) {
        var $node = this._getNodeFromTypeAndId(type, id);
        
        if(type !== MenuNodeType.Folder){
            $node.children('.unread_items_counter').html(unreadCount);
        }

        if(unreadCount === 0){
            $node.addClass('all_read');
        } else {
            $node.removeClass('all_read hidden');
        }
    };

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

                var feedId = parseInt($dragged.data('id'), 10);
                var folderId = parseInt($dropped.data('id'), 10);
                var fromFolderId = parseInt($dragged.parent().data('id'), 10);

                // ignore when dragged to the same folder
                if(folderId === fromFolderId){
                    return;
                }

                // adjust unreadcount for the old folder
                var feedUnreadCount = self._unreadCount.Feed[feedId];
                if(fromFolderId !== 0){
                    self._setUnreadCount(MenuNodeType.Feed, feedId, 0);
                }

                if($dropped.hasClass(self._menuNodeTypeToClass(MenuNodeType.Folder))){
                    $dropped.children('ul').append($dragged[0]);
                } else {
                    $dropped.append($dragged[0]);
                }

                // adjust unreadcount for the new folder
                if(folderId !== 0){
                    self._setUnreadCount(MenuNodeType.Feed, feedId, feedUnreadCount);
                }

                self._resetOpenFolders();
                self._moveFeedToFolder(feedId, folderId);

                // in case jquery ui did something weird
                $('.dnd_over').removeClass('dnd_over');
            }
        });
    };

    /**
     * Moves a feed to a folder
     * @param feedId the feed that should be moved (can only be a feed)
     * @param id the id of the folder where it should be moved, 0 for root
     */
    Menu.prototype._moveFeedToFolder = function(feedId, folderId){
        data = {
            feedId: feedId,
            folderId: folderId
        };

        $.post(OC.filePath('news', 'ajax', 'movefeedtofolder.php'), data, function(jsonData) {
            if(jsonData.status !== 'success'){
                OC.dialogs.alert(jsonData.data.message, t('news', 'Error'));
            }
        });
    };


})();