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
 * This file includes the cache and access for feed items
 */

var News = News || {};
var t = t || function(app, string){ return string; }; // mock translation for local testing

(function(){

    /*##########################################################################
     * Items
     *########################################################################*/
    /**
     * Creates a new item instance and tells it to put the items into
     * the selected div
     * @param cssSelector the selector of the div which holds the ul for the feeds
     */
    var Items = function(cssSelector){
        var self = this;
        this._$articleList = $(cssSelector);
        this._$articleList.scrollTop(0);
        this._itemCache = new ItemCache();

        // this array is used to store ids to prevent sending too
        // many posts when scrolling. the structure is: feed_id: boolean
        this._processing = {};
        
        // mark items whose title was hid under the top edge as read
        // when the bottom is reached, mark all items as read
        this._$articleList.scroll(function(){
            var boxHeight = $(this).height();
            var scrollHeight = $(this).prop('scrollHeight');
            var scrolled = $(this).scrollTop() + boxHeight;
            $(this).children('ul').children('.feed_item:not(.read)').each(function(){
                var item = this;
                var itemOffset = $(this).position().top;
                if(itemOffset <= 0 || scrolled >= scrollHeight){
                    // wait and check if the item is still under the top edge
                    setTimeout(function(){ self._markItemAsReadTimeout(item);}, 1000);
                }
            })
        });

        this._itemCache.populate(this._$articleList.children('ul'));
    }

    /**
     * Marks an item as read which is called by the timeout
     * @param item the dom item
     */
    Items.prototype._markItemAsReadTimeout = function(item) {
        var itemId = parseInt($(item).data('id'));
        var itemOffset = $(item).position().top;
        var boxHeight = this._$articleList.height();
        var scrollHeight = this._$articleList.prop('scrollHeight');
        var scrolled = this._$articleList.scrollTop() + boxHeight;
        if(itemOffset < 0 || scrolled >= scrollHeight){
            if(this._processing[itemId] === undefined || this._processing[itemId] === false){
                // mark item as processing to prevent unecessary post requests  
                this._processing[itemId] = true;
                var handler = new News.ItemStatusHandler(itemId);
                handler.setRead(true);  
            }
        } 
    };

    /**
     * Loads the feeds into the righ view
     * @param type the type (MenuNodeType)
     * @param id the id
     * @param onSuccessCallback a callback that is executed when the loading succeeded
     */
    Items.prototype.load = function(type, id, onSuccessCallback) {
        var self = this;
        var data = {
            feedId: id,
            feedType: type,
            getMostRecentItemId: this._itemCache.getMostRecentItemId(type, id)
        };

        this._$articleList.addClass('loading');
        this._$articleList.children('ul').hide();

        $.post(OC.filePath('news', 'ajax', 'loadfeed.php'), data, function(jsonData) {
            if(jsonData.status == 'success'){
                self._$articleList.empty()
                self._itemCache.populate(jsonData.data.feedItems);

                var $items = self._itemCache.getFeedHtml(type, id);
                self._$articleList.append($items);
                self._$articleList.scrollTop(0);
                onSuccessCallback();
            } else {
                OC.dialogs.alert(t('news', 'Error while loading the feed'), t('news', 'Error'));
                self._$articleList.children('ul').show();
            }
            self._$articleList.removeClass('loading');
        });
    };

    /**
     * Empties the item cache
     */
    Items.prototype.emptyItemCache = function() {
        this._itemCache.empty();
    };

    /**
     * Returns the most recent id of a feed from the cache
     * @param type the type (MenuNodeType)
     * @param id the id
     * @return the most recent id that is loaded on the page or 0
     */
    Items.prototype.getMostRecentItemId = function(type, id) {
        return this._itemCache.getMostRecentItemId(type, id);
    };

    /**
     * Returns a jquery node by searching for its id
     * @param id the id of the node
     * @return the jquery node
     */
    Items.prototype._findNodeById = function(id) {
        id = parseInt(id);
        return this._$articleList.find('.feed_item[data-id="' + id + '"]');
    };


    Items.prototype._toggleImportant = function(itemId) {
        var $currentItem = $()
        var $currentItemStar = $currentItem.children('.utils').children('.primary_item_utils').children('.star');
        var important = $currentItemStar.hasClass('important');
        if(_important){
            status = 'unimportant';
        } else {
            status = 'important';
        }

        var data = {
            itemId: _itemId,
            status: status
        };

        $.post(OC.filePath('news', 'ajax', 'setitemstatus.php'), data, function(jsondata){
            if(jsondata.status == 'success'){
                if(_important){
                    _$currentItemStar.removeClass('important'); 
                } else {
                    _$currentItemStar.addClass('important');
                }
            } else{
                OC.dialogs.alert(jsondata.data.message, t('news', 'Error'));
            }
        });
    };

    News.Items = Items;


    /*##########################################################################
     * ItemCache
     *########################################################################*/
    /**
     * A cache which holds the items of all loaded feeds
     */
    var ItemCache = function() {
        this._items = {};
        this._feeds = {};
    }

    /**
     * Adds Html elements to the cache
     * @param html the html for a complete list with items
     */
    ItemCache.prototype.populate = function(html) {
        var self = this;
        $html = $(html);
        $html.children('.feed_item').each(function(){
            var item = new Item(this);
            self._items[item.getId()] = item;
            self._feeds[item.getFeedId()] = self._feeds[item.getFeedId()] || {};
            self._feeds[item.getFeedId()][item.getId()] = item;
        });
    };

    /**
     * Empties the cache
     */
    ItemCache.prototype.empty = function() {
        this._items = {};
        this._feeds = {};
    };

    /**
     * Returns all the ids of feeds for a type sorted by id ascending
     * @param type the type (MenuNodeType)
     * @param id the id
     * @return all the ids of feeds for a type sorted by id ascending
     */
    ItemCache.prototype._getSortedItemIds = function(type, id) {
        var itemIds = new Array();
        if(Object.keys(this._feeds).length === 0 || Object.keys(this._items).length === 0){
            return itemIds;
        }

        switch(type){

            case MenuNodeType.Feed:
                if(this._feeds[id] === undefined){
                    return itemIds;
                }
                $.each(this._feeds[id], function(key, value){
                    itemIds.push(value.getId());
                });
                break;

            case MenuNodeType.Folder:
                // this is a bit of a hack and not that beautiful^^
                var feedIds = News.Objects.Menu.getFeedIdsOfFolder(id);
                for(var i=0; i<feedIds.length; i++){
                    itemIds.concat(this._getSortedItemIds(MenuNodeType.Feed, feedIds[i]));
                }
                break;

            case MenuNodeType.Subscriptions:
                $.each(this._items, function(key, value){
                    itemIds.push(value.getId());
                });
                break;

            case MenuNodeType.Starred:
                $.each(this._items, function(key, value){
                    if(value.isStarred()){
                        itemIds.push(value.getId());
                    }
                });
                break;
        }
        return itemIds.sort();
    };

    /**
     * Returns the most recent id of a feed
     * @param type the type (MenuNodeType)
     * @param id the id
     * @return the most recent id that is loaded on the page or 0
     */
    ItemCache.prototype.getMostRecentItemId = function(type, id) {
        var itemIds = this._getSortedItemIds(type, id);
        if(itemIds.length === 0){
            return 0;
        } else {
            return itemIds[itemIds.length-1];
        }
    };

    /**
     * Returns the html for a specific feed
     * @param type the type (MenuNodeType)
     * @param id the id
     * @return the jquery html element for a complete feed
     */
    ItemCache.prototype.getFeedHtml = function(type, id) {
        var itemIds = this._getSortedItemIds(type, id);
        itemIds.reverse(); // reverse for showing newest item first
        var $html = $('<ul>');
        for(var i=0; i<itemIds.length; i++){
            $html.append(this._items[itemIds[i]].getHtml());
        }
        return $html;
    };

    /*##########################################################################
     * Item
     *########################################################################*/
    /**
     * An item which binds the appropriate html and event handlers
     * @param html the html to populate the item
     */
     var Item = function(html){
        this._starred = false;
        this._$html = $(html);
        this._bindItemEventListeners();
        this._id = parseInt(this._$html.data('id'));
        this._feedId = parseInt(this._$html.data('feedid'));
     }

    /**
     * Returns the html code for the element
     * @return the html for the item
     */
    Item.prototype.render = function() {
        return this._$html[0];
    };

    /**
     * Returns the id of an item
     * @return the id of the item
     */
    Item.prototype.getId = function() {
        return this._id;
    };


    /**
     * Returns the feedid of an item
     * @return the feeid of the item
     */
    Item.prototype.getFeedId = function() {
        return this._feedId;
    };

    /**
     * Returns the html of an item
     * @return the jquery html of the item
     */
    Item.prototype.getHtml = function() {
        return this._$html;
    };

    /**
     * Returns true if an item is starred
     * @return true if starred, otherwise false
     */
    Item.prototype.isStarred = function() {
        return this._starred;
    };

        // FIXME
    /**
     * Binds a listener on the feed item list to detect scrolling and mark previous
     * items as read
     */
    Item.prototype._bindItemEventListeners = function() {
        var self = this;

        // single hover on item should mark it as read too
        this._$html.find('#h1.item_title a').click(function(){
            var $item = $(this).parent().parent('.feed_item');
            var itemId = $item.data('id');
            var handler = new News.ItemStatusHandler(itemId);
            handler.setRead(true);
        });

        // single hover on item should mark it as read too
        this._$html.find('.body').click(function(){
            var $item = $(this).parent('.feed_item');
            var itemId = $item.data('id');
            var handler = new News.ItemStatusHandler(itemId);
            handler.setRead(true);
        });

        // mark or unmark as important
        this._$html.find('li.star').click(function(){
            var $item = $(this).parent().parent().parent('.feed_item');
            var itemId = $item.data('id');
            self._toggleImportant(itemId);
        });

        // toggle logic for the keep unread handler
        this._$html.find('.keep_unread').click(function(){
            var $item = $(this).parent().parent().parent('.feed_item');
            var itemId = $item.data('id');
            var handler = new News.ItemStatusHandler(itemId);
            handler.toggleKeepUnread();
        });

        this._$html.find('.keep_unread input[type=checkbox]').click(function(){
            var $item = $(this).parent().parent().parent().parent('.feed_item');
            var itemId = $item.data('id');
            var handler = new News.ItemStatusHandler(itemId);
            handler.toggleKeepUnread();
        });

        this._$html.find('time.timeago').timeago();
    };

})();




// TODO: integrate This

    /**
     * This handler handles changes in the ui when the itemstatus changes
     */
    News.ItemStatusHandler = function(itemId){
        var _itemId = parseInt(itemId);
        
        var _feedId = _$currentItem.data('feedid');
        var _read = _$currentItem.hasClass('read');

        /**
         * Switches important items to unimportant and vice versa
         */
        var _toggleImportant = function(){

        };

        /**
         * Checks the state of the keep read checkbox
         * @return true if its checked, otherwise false
         */
        var _isKeptRead = function(){
            var _$currentItemKeepUnread = _$currentItem.children('.bottom_utils').children('.secondary_item_utils').children('.keep_unread').children('input[type=checkbox]');
            return _$currentItemKeepUnread.prop('checked');
        }

        /**
         * Toggles an item as "keep unread". This prevents all handlers to mark it as unread
         * except the current one
         */
        var _toggleKeepUnread = function(){
            var _$currentItemKeepUnread = _$currentItem.children('.bottom_utils').children('.secondary_item_utils').children('.keep_unread').children('input[type=checkbox]');
            if(_isKeptRead()){
                _$currentItemKeepUnread.prop("checked", false);
            } else {
                _$currentItemKeepUnread.prop("checked", true);
                News.Feed.processing[_itemId] = true;
                _setRead(false);
            }
        };

        /**
         * Sets the current item as read or unread
         * @param read true sets the item to read, false to unread
         */
        var _setRead = function(read){
            var status;

            // if we already have the status, do nothing
            if(read === _read){
                News.Feed.processing[_itemId] = false;
                return;
            }
            // check if the keep unread flag was set
            if(read && _isKeptRead()){
                News.Feed.processing[_itemId] = false;
                return; 
            } 

            if(read){
                status = 'read';
            } else {
                status = 'unread';
            }

            var data = {
                itemId: _itemId,
                status: status
            };

            $.post(OC.filePath('news', 'ajax', 'setitemstatus.php'), data, function(jsonData){
                if(jsonData.status == 'success'){
                    var feedHandler = new News.FeedStatusHandler(_feedId);
                    if(!_$currentItem.hasClass('read') && read){
                        _$currentItem.addClass('read');
                        feedHandler.decrrementUnreadCount();
                    } else if(_$currentItem.hasClass('read') && !read){
                        _$currentItem.removeClass('read');
                        feedHandler.incrementUnreadCount();
                    }
                } else {
                    OC.dialogs.alert(jsonData.data.message, t('news', 'Error'));
                }
                News.Feed.processing[_itemId] = false;
            })
            
        };

        // set public methods
        this.setRead = function(read){ _setRead(read); };
        this.isRead = function(){ return _read; };
        this.toggleImportant = function(){ _toggleImportant(); };
        this.toggleKeepUnread = function(){ _toggleKeepUnread(); };
    };



