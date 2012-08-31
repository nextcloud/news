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

    var Items = function(cssSelector){
        this._$articleList = $(cssSelector);
        this._$articleList.scrollTop(0);
    }

    Items.prototype.length = function(type, id) {
        return 0;
    };

    Items.prototype.mostRecentItemId = function(type, id) {
        return 0;
    };

    News.Items = Items;
})();




// TODO: integrate This
/**
 * Binds a listener on the feed item list to detect scrolling and mark previous
 * items as read
 */
function bindItemEventListeners(){

    // single hover on item should mark it as read too
    $('#feed_items h1.item_title a').click(function(){
        var $item = $(this).parent().parent('.feed_item');
        var itemId = $item.data('id');
        var handler = new News.ItemStatusHandler(itemId);
        handler.setRead(true);
    });

    // single hover on item should mark it as read too
    $('#feed_items .body').click(function(){
        var $item = $(this).parent('.feed_item');
        var itemId = $item.data('id');
        var handler = new News.ItemStatusHandler(itemId);
        handler.setRead(true);
    });

    // mark or unmark as important
    $('#feed_items li.star').click(function(){
        var $item = $(this).parent().parent().parent('.feed_item');
        var itemId = $item.data('id');
        var handler = new News.ItemStatusHandler(itemId);
        handler.toggleImportant();
    });

    // toggle logic for the keep unread handler
    $('#feed_items .keep_unread').click(function(){
        var $item = $(this).parent().parent().parent('.feed_item');
        var itemId = $item.data('id');
        var handler = new News.ItemStatusHandler(itemId);
        handler.toggleKeepUnread();
    });
    $('#feed_items .keep_unread input[type=checkbox]').click(function(){
        var $item = $(this).parent().parent().parent().parent('.feed_item');
        var itemId = $item.data('id');
        var handler = new News.ItemStatusHandler(itemId);
        handler.toggleKeepUnread();
    });

    // bind the mark all as read button
    $('#mark_all_as_read').unbind();
    $('#mark_all_as_read').click(function(){
        var feedId = News.Feed.activeFeedId;
        News.Feed.setAllItemsRead(feedId);
    });

    $("time.timeago").timeago();

}


/**
 * Marks an item as read which is called by the timeout
 * @param item the dom item
 */
function markItemAsRead(scrollArea, item){
    var itemId = parseInt($(item).data('id'));
    var itemOffset = $(item).position().top;
    var boxHeight = $(scrollArea).height();
    var scrollHeight = $(scrollArea).prop('scrollHeight');
    var scrolled = $(scrollArea).scrollTop() + boxHeight;
    if(itemOffset < 0 || scrolled >= scrollHeight){
        if(News.Feed.processing[itemId] === undefined || News.Feed.processing[itemId] === false){
            // mark item as processing to prevent unecessary post requests  
            News.Feed.processing[itemId] = true;
            var handler = new News.ItemStatusHandler(itemId);
            handler.setRead(true);  
        }
    } 
}

    /**
     * This handler handles changes in the ui when the itemstatus changes
     */
    ItemStatusHandler = function(itemId){
        var _itemId = parseInt(itemId);
        var _$currentItem = $('#feed_items li[data-id="' + itemId + '"]');
        var _feedId = _$currentItem.data('feedid');
        var _read = _$currentItem.hasClass('read');

        /**
         * Switches important items to unimportant and vice versa
         */
        var _toggleImportant = function(){
            var _$currentItemStar = _$currentItem.children('.utils').children('.primary_item_utils').children('.star');
            var _important = _$currentItemStar.hasClass('important');
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

    // mark items whose title was hid under the top edge as read
    // when the bottom is reached, mark all items as read
    $('#feed_items').scroll(function(){
        var boxHeight = $(this).height();
        var scrollHeight = $(this).prop('scrollHeight');
        var scrolled = $(this).scrollTop() + boxHeight;
        var scrollArea = this;
        $(this).children('ul').children('.feed_item:not(.read)').each(function(){
            var item = this;
            var itemOffset = $(this).position().top;
            if(itemOffset <= 0 || scrolled >= scrollHeight){
                // wait and check if the item is still under the top edge
                setTimeout(function(){ markItemAsRead(scrollArea, item);}, 1000);
            }
        })

    });

    $(document).keydown(function(e) {
        if ((e.keyCode || e.which) == 74) { // 'j' key shortcut
            
        }
    });