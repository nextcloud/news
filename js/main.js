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

    // config values
    var menuUpdateIntervalMiliseconds = 200000;

    // global object array for accessing instances
    News.Objects = {};
    News.Objects.Items = new News.Items('#feed_items');
    News.Objects.Menu = new News.Menu(menuUpdateIntervalMiliseconds, News.Objects.Items);
    News.Objects.Menu.bindOn('#feeds > ul');

    /* first run script begins */
    $('#browsebtn_firstrun, #cloudbtn_firstrun, #importbtn_firstrun').hide();
    
    /* first run script ends */

    $('#addfeed').click(function() {
        $('#addfeed_dialog').dialog('open');
        $('#feed_add_url').html('');

        // populate folderlist
        $('#addfeed_dialog .menu').empty();
        
        // http://9gag.com/trending

        var $rootFolder = $('<li>').addClass('menuItem').html($('<b>').html(t('News', 'None')));
        $rootFolder.click(function(){
            News.DropDownMenu.selectItem(this, 0);
        });
        $('#addfeed_dialog .menu').append($rootFolder);        

        $('#feeds .folder').each(function(){
            var title = $(this).children('.title').html();
            var id = parseInt($(this).data('id'));
            var $folder = $('<li>').addClass('menuItem').html(title);
            $folder.click(function(){
                News.DropDownMenu.selectItem(this, id);
            });
            $('#addfeed_dialog .menu').append($folder);
        });
    });
    
    $('#addfolder').click(function() {
        $('#addfolder_dialog').dialog('open');
        $('#folder_add_name').val('');
    });

    $('#addfeedbtn').click(function() {
        $(this).hide();
        $('#addfeed_dialog_firstrun').show();
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

    $('#addfolder_dialog,#addfeed_dialog').dialog({
        dialogClass:'dialog',
        minWidth: 600,
        autoOpen: false
    }).css('overflow','visible');

    $('#folder_add_submit').click(function(){
        News.Folder.submit(this);
    });

    $('.dropdownBtn').click(function(){
        News.DropDownMenu.dropdown(this);
    });

    $('#feed_add_submit').click(function(){
        News.Feed.submit(this);
    });

    $('#view').click(function(){
        var data = {};
        var showAll;
        if($(this).hasClass('show_all')){
            data.show = 'unread';
            showAll = true;
            $(this).addClass('show_unread').removeClass('show_all');
        } else {
            data.show  = 'all';
            showAll = false;
            $(this).addClass('show_all').removeClass('show_unread');
        }
        News.Objects.Menu.setShowAll(showAll);

        $.post(OC.filePath('news', 'ajax', 'usersettings.php'), data, function(jsondata){
            if(jsondata.status == 'success'){

            } else {
                OC.dialogs.alert(jsonData.data.message, t('news', 'Error'));
            }
        });
    }); 
    
    $(document).click(function(event) {
        $('#feedfoldermenu').hide();
    });

    $(document).keydown(function(e) {
        if ((e.keyCode || e.which) == 74) { // 'j' key shortcut

        }
    });

});

