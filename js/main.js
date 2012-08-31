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
        var data = {};
        if($(this).hasClass('show_all')){
            data.show = 'unread';
            $(this).addClass('show_unread').removeClass('show_all');
        } else {
            data.show  = 'all';
            $(this).addClass('show_all').removeClass('show_unread');
        }

        $.post(OC.filePath('news', 'ajax', 'usersettings.php'), data, function(jsondata){
            if(jsondata.status == 'success'){
                var showAll;
                if(data.show === 'all'){
                    showAll = true;
                } else {
                    showAll = false;
                }
                News.Objects.Menu.setShowAll(showAll);
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

