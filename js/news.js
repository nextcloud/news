News={
	DropDownMenu: {
		fade:function(menu){
			$(menu).toggle();
			return false;
		},
		dropdown:function(button){
			var list = $(button).parent().find('ul#dropdownmenu');
			if (list.css('display') == 'none')
				list.slideDown('fast').show();
			else
				list.slideUp('fast');

			return false;
		},
		selectItem:function(item, folderid){
			var parent = $(item).parent().parent();
			parent.find('#dropdownBtn').text($(item).text());
			parent.find(':input[name="folderid"]').val(folderid);
			parent.find('ul#dropdownmenu').slideUp('fast');
		}
	},
	UI: {
		overview:function(dialogtype, dialogfile){
		    	if($(dialogtype).dialog('isOpen') == true){
				$(dialogtype).dialog('moveToTop');
			}else{
				$('#dialog_holder').load(OC.filePath('news', 'ajax', dialogfile), function(jsondata){
					if(jsondata.status != 'error'){
						$(dialogtype).dialog({
							dialogClass:'dialog',
							minWidth: 600,
							close: function(event, ui) {
								$(this).dialog('destroy').remove();
							}
						}).css('overflow','visible');
					} else {
						alert(jsondata.data.message);
					}
				});
			}
			return false;
		}
	},
	Folder: {
		submit:function(button){
			var displayname = $("#folder_add_name").val().trim();

			if(displayname.length == 0) {
				OC.dialogs.alert(t('news', 'Name of the folder cannot be empty.'), t('news', 'Error'));
				return false;
			}

			$(button).attr("disabled", true);
			$(button).prop('value', t('news', 'Adding...'));

			var folderid = $('#inputfolderid:input[name="folderid"]').val();

			var url;
			url = OC.filePath('news', 'ajax', 'createfolder.php');

			$.post(url, { name: displayname, parentid: folderid },
				function(jsondata){
					if(jsondata.status == 'success'){
						$('div[data-id="' + folderid + '"] > ul').append(jsondata.data.listfolder);
						setupFeedList();
						//OC.dialogs.confirm(t('news', 'Do you want to add another folder?'), t('news', 'Folder added!'), function(answer) {
						//	if(!answer) {
								$('#addfolder_dialog').dialog('destroy').remove();
						//	}
						//});
					} else {
						OC.dialogs.alert(jsondata.data.message, t('news', 'Error'));
					}
					$("#folder_add_name").val('');
					$(button).attr("disabled", false);
					$(button).prop('value', t('news','Add folder'));
			});
		},
		'delete':function(folderid) {
			$('#feeds_delete').tipsy('hide');
			OC.dialogs.confirm(t('news', 'Are you sure you want to delete this folder and all its feeds?'), t('news', 'Warning'), function(answer) {
				if(answer == true) {
					var rightcontent = $('div.rightcontent');
					var shownfeedid = rightcontent.attr('data-id');
					$.post(OC.filePath('news', 'ajax', 'deletefolder.php'),{'folderid':folderid, 'shownfeedid':shownfeedid},function(jsondata){
						if(jsondata.status == 'success'){
							$('div.collapsable_container[data-id="' + jsondata.data.folderid + '"]').remove();
							if(jsondata.data.part_items) {
								rightcontent.empty();
								rightcontent.html(jsondata.data.part_items);
							}
						}
						else{
							OC.dialogs.alert(jsondata.data.message, t('news', 'Error'));
						}
					});
				}
			});
			return false;
		}
	},
	Feed: {
		id:'',
		submit:function(button){
			var feedurl = $("#feed_add_url").val().trim();

			if(feedurl.length == 0) {
				OC.dialogs.alert(t('news', 'URL cannot be empty.'), t('news', 'Error'));
				return false;
			}

			$(button).attr("disabled", true);
			$(button).prop('value', t('news', 'Adding...'));

			var folderid = $('#inputfolderid:input[name="folderid"]').val();

			$.post(OC.filePath('news', 'ajax', 'createfeed.php'), { feedurl: feedurl, folderid: folderid },
				function(jsondata){
					if(jsondata.status == 'success'){
						$('div[data-id="' + folderid + '"] > ul').append(jsondata.data.listfeed);
						setupFeedList();
						OC.dialogs.confirm(t('news', 'Do you want to add another feed?'), t('news', 'Feed added!'), function(answer) {
							if(!answer) {
								$('#addfeed_dialog').dialog('destroy').remove();
								var rightcontent = $('div.rightcontent');
								rightcontent.empty();
								rightcontent.html(jsondata.data.part_items);
								setupRightContent();
							}
						});
					} else {
						OC.dialogs.alert(jsondata.data.message, t('news', 'Error'));
					}
				$("#feed_add_url").val('');
				$(button).attr("disabled", false);
				$(button).prop('value', t('news', 'Add feed'));
			});

		},
		'delete':function(feedid) {
			$('#feeds_delete').tipsy('hide');
			OC.dialogs.confirm(t('news', 'Are you sure you want to delete this feed?'), t('news', 'Warning'), function(answer) {
				if(answer == true) {
					$.post(OC.filePath('news', 'ajax', 'deletefeed.php'),{'feedid':feedid},function(jsondata){
						if(jsondata.status == 'success'){
							$('li.feeds_list[data-id="'+jsondata.data.feedid+'"]').remove();
							var rightcontent = $('div.rightcontent');
							if(rightcontent.attr('data-id') == feedid) {
								rightcontent.empty();
								rightcontent.html(jsondata.data.part_items);
							}
						}
						else{
							OC.dialogs.alert(jsondata.data.message, t('news', 'Error'));
						}
					});
				}
			});
			return false;
		},
		markItem:function(itemid, feedid) {
			var currentitem = $('#rightcontent [data-id="' + itemid + '"]');
			if (currentitem.hasClass('title_unread')) {
				$.post(OC.filePath('news', 'ajax', 'markitem.php'),{'itemid':itemid},function(jsondata){
					if(jsondata.status == 'success'){
						currentitem.removeClass('title_unread');
						currentitem.addClass('title_read');

						// decrement counter
						var counterplace = $('.feeds_list[data-id="'+feedid+'"]').find('#unreaditemcounter');
						var oldcount = counterplace.html();
						counterplace.empty();
						if (oldcount <= 1) {
							counterplace.removeClass('nonzero').addClass('zero');
						}
						else {
							counterplace.append(--oldcount);
						}
						//set a timeout for this
					}
					else{
						OC.dialogs.alert(jsondata.data.message, t('news', 'Error'));
					}
				})
			};
		},
		updateAll:function() {
			$.post(OC.filePath('news', 'ajax', 'feedlist.php'),function(jsondata){
				if(jsondata.status == 'success'){
					var feeds = jsondata.data;
					for (var i = 0; i < feeds.length; i++) {
						News.Feed.update(feeds[i]['id'], feeds[i]['url'], feeds[i]['folderid']);
					}
				}
				else {
					//TODO:handle error case
				}
			});
		},
		update:function(feedid, feedurl, folderid) {
			var counterplace = $('.feeds_list[data-id="'+feedid+'"]').find('#unreaditemcounter');
			var oldcount = counterplace.html();
			counterplace.removeClass();
			counterplace.html('<img style="vertical-align: middle;" src="' + OC.imagePath('core','loader.gif') + '" alt="refresh" />');
			$.post(OC.filePath('news', 'ajax', 'updatefeed.php'),{'feedid':feedid, 'feedurl':feedurl, 'folderid':folderid},function(jsondata){
				if(jsondata.status == 'success'){
					var newcount = jsondata.data.unreadcount;
					if (newcount > 0) {
						counterplace.addClass('nonzero');
						counterplace.html(newcount);
					}
					else {
						counterplace.addClass('zero');
						counterplace.html('');
					}
				}
				else{
				  	if (oldcount > 0) {
						counterplace.addClass('nonzero');
						counterplace.html(oldcount);
					}
				}

			});
		}
	}
}

function collapsable_trigger(trigger, items) {
	var triggericon = OC.imagePath('core', 'actions/triangle-s.svg');
	trigger.css('background-image', 'url(' + triggericon + ')');
	if (items.css('display') == 'block') {
		trigger.css('-moz-transform', 'none');
		trigger.css('transform', 'none');
	}
	else {
		trigger.css('-moz-transform', 'rotate(-90deg)');
		trigger.css('transform', 'rotate(-90deg)');
	}
}

function setupFeedList() {
	$('.collapsable_trigger').click(function(){
		var items = $(this).parent().parent().children('ul');
		items.toggle();
		collapsable_trigger($(this),items);
	});

	var list = $('.collapsable,.feeds_list').hover(
		function() {
			$(this).find('#feeds_delete,#feeds_edit').css('display', 'inline');
			$(this).find('#unreaditemcounter').css('display', 'none');

			var trigger = $(this).find('.collapsable_trigger');
			var items = trigger.parent().parent().children('ul');
			collapsable_trigger(trigger, items);
		},
		function() {
			$(this).find('#feeds_delete,#feeds_edit').css('display', 'none');
			$(this).find('#unreaditemcounter').css('display', 'inline');
			var foldericon = OC.imagePath('core', 'places/folder.svg');
			var trigger = $(this).find('.collapsable_trigger');
			trigger.css('background-image', 'url(' + foldericon + ')');
			trigger.css('-moz-transform', 'none');
			trigger.css('transform', 'none');
		}
	);
	list.find('#feeds_delete').hide();
	list.find('#feeds_edit').hide();
	list.find('#unreaditemcounter').show();
}

function setupRightContent() {
	$('.accordion .title_unread').click(function() {
		$(this).next().toggle();
		return false;
	}).next().hide();

	$('.accordion .title_read').click(function() {
		$(this).next().toggle();
		return false;
	}).next().hide();
}

$(document).ready(function(){

	$('#addfeed').click(function() {
		News.UI.overview('#addfeed_dialog','feeddialog.php');
	});

	$('#addfolder').click(function() {
		News.UI.overview('#addfolder_dialog','folderdialog.php');
	});

	$('#addfeedfolder').click(function(event) {
	      event.stopPropagation();
	});

	$('#settingsbtn').on('click keydown', function() {
		try {
			OC.appSettings({appid:'news', loadJS:true});
		} catch(e) {
			alert(e);
		}
	});

	setupFeedList();
	setupRightContent();

	News.Feed.updateAll();
	var updateInterval = 200000; //how often the feeds should update (in msec)
	setInterval('News.Feed.updateAll()', updateInterval);
});

$(document).click(function(event) {
	$('#feedfoldermenu').hide();
});

