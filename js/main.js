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


var News = News || {};

$(document).ready(function(){

    // global object array for accessing instances
    News.Objects = {};
    News.Objects.Menu = new News.Menu($('#view').hasClass('show_all'));
    News.Objects.Items = new News.Items();

    News.Objects.Menu.bindOn('#feeds ul');

    // basic setup
    News.Feed.updateAll();
    var updateInterval = 200000; //how often the feeds should update (in msec)
    setInterval('News.Feed.updateAll()', updateInterval);

    // bind listeners on the menu
    


    /* first run script begins */
    $('#browsebtn_firstrun, #cloudbtn_firstrun, #importbtn_firstrun').hide();
    
    /* first run script ends */

    $('#addfeed').click(function() {
        News.UI.overview('#addfeed_dialog','feeddialog.php');
    });
    
    $('#addfeedbtn').click(function() {
        $(this).hide();
        $('#addfeed_dialog_firstrun').show();
    });
    
    $('#addfolder').click(function() {
        News.UI.overview('#addfolder_dialog','folderdialog.php');
    });

    $('#addfeedfolder').click(function(event) {
        News.DropDownMenu.fade($(this).children('ul'));
        event.stopPropagation();
    });

    $('#settingsbtn').on('click keydown', function() {
        try {
            OC.appSettings({appid:'news', loadJS:true, cache:false});
        } catch(e) {
            alert(e);
        }
    });

    $('#view').click(function(){
        var term;
        if($(this).hasClass('show_all')){
            term = 'unread';
            $(this).addClass('show_unread').removeClass('show_all');
        } else {
            term = 'all';
            $(this).addClass('show_all').removeClass('show_unread');
        }
        News.Feed.filter(term);
    });

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
    
    $('#feed_items').scrollTop(0);
    
    $(document).keydown(function(e) {
        if ((e.keyCode || e.which) == 74) { // 'j' key shortcut
            
        }
    }); 

});
