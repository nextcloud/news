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
						$('.collapsable_container[data-id="' + folderid + '"] > ul').append(jsondata.data.listfolder);
						setupFeedList();
						transformCollapsableTrigger();
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
			$('.feeds_delete').tipsy('hide');
			OC.dialogs.confirm(t('news', 'Are you sure you want to delete this folder and all its feeds?'), t('news', 'Warning'), function(answer) {
				if(answer == true) {
					var rightcontent = $('div.rightcontent');
					var shownfeedid = rightcontent.attr('data-id');
					$.post(OC.filePath('news', 'ajax', 'deletefolder.php'),{'folderid':folderid, 'shownfeedid':shownfeedid},function(jsondata){
						if(jsondata.status == 'success'){
							$('.collapsable_container[data-id="' + jsondata.data.folderid + '"]').remove();
							if(jsondata.data.part_items) {
								rightcontent.empty();
								rightcontent.html(jsondata.data.part_items);
							}
							transformCollapsableTrigger();
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

			$.ajax({
				type: "POST",
				url: OC.filePath('news', 'ajax', 'createfeed.php'),
				data: { 'feedurl': feedurl, 'folderid': folderid },
				dataType: "json",
				success: function(jsondata){
					if(jsondata.status == 'success'){
						$('.collapsable_container[data-id="' + folderid + '"] > ul').append(jsondata.data.listfeed);
						setupFeedList();
						News.Feed.load(jsondata.data.feedid);

						OC.dialogs.confirm(t('news', 'Do you want to add another feed?'), t('news', 'Feed added!'), function(answer) {
							if(!answer) {
								$('#addfeed_dialog').dialog('destroy').remove();
								$('ul.accordion').before(jsondata.data.part_newfeed);
							}
						});
					} else {
						OC.dialogs.alert(jsondata.data.message, t('news', 'Error'));
					}
					$("#feed_add_url").val('');
					$(button).attr("disabled", false);
					$(button).prop('value', t('news', 'Add feed'));
				},
				error: function(xhr) {
					OC.dialogs.alert(t('news', 'Error while parsing the feed'), t('news', 'Fatal Error'));
					$("#feed_add_url").val('');
					$(button).attr("disabled", false);
					$(button).prop('value', t('news', 'Add feed'));
				}
			});
		},
		'delete':function(feedid) {
			$('.feeds_delete').tipsy('hide');
			OC.dialogs.confirm(t('news', 'Are you sure you want to delete this feed?'), t('news', 'Warning'), function(answer) {
				if(answer == true) {
					$.post(OC.filePath('news', 'ajax', 'deletefeed.php'),{'feedid':feedid},function(jsondata){
						if(jsondata.status == 'success'){
							$('li.feed[data-id="'+jsondata.data.feedid+'"]').remove();

							var rightcontent = $('div.rightcontent');
							if(rightcontent.attr('data-id') == feedid) {
								rightcontent.find('div#feedadded').remove();
								rightcontent.find('ul.accordion').before(jsondata.data.part_items);
								transformCollapsableTrigger();
								// if the deleted feed is the current feed, reload the page
								window.location.reload();
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
						var counterplace = $('li.feed[data-id="'+feedid+'"]').find('.unreaditemcounter');
						var title = $('li.feed[data-id="'+feedid+'"] > a');
						var oldcount = counterplace.html();
						counterplace.empty();
						if (oldcount <= 1) {
							counterplace.removeClass('nonzero').addClass('zero');
							title.removeClass('nonzero').addClass('zero');
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
		load:function(feedid) {
			$.post(OC.filePath('news', 'ajax', 'loadfeed.php'),{'feedid':feedid},function(jsondata) {
				if(jsondata.status == 'success'){
					var rightcontent = $('div.rightcontent');
					rightcontent.empty();
					rightcontent.attr('data-id', feedid);
					rightcontent.html(jsondata.data.items_header + jsondata.data.part_items);

					$('li#selected_feed').attr('id', '');
					$('li.feed[data-id="' + feedid + '"]').attr('id', 'selected_feed');

					transformCollapsableTrigger();
				}
				else {
					OC.dialogs.alert(t('news', 'Error while loading the feed'), t('news', 'Error'));
				}
			});
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
			var counterplace = $('.feed[data-id="'+feedid+'"]').find('.unreaditemcounter');
			var oldcount = counterplace.html();
			counterplace.removeClass('zero nonzero');
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

function transformCollapsableTrigger() {
	// we need this here to detect and toggle new children instantly
	$('.collapsable_trigger').unbind();
	$('.collapsable_trigger').click(function(){
		var items = $(this).parent().parent().children('ul');
		items.toggle();
		transformCollapsableTrigger();
	});

	var triggericon = OC.imagePath('core', 'actions/triangle-s.svg');
	var foldericon = OC.imagePath('core', 'places/folder.svg');

	$('.collapsable_trigger').each(
		function() {
			var items = $(this).parent().parent().children('ul');
			if (items.html()) {
				$(this).css('background-image', 'url(' + triggericon + ')');
				if (items.css('display') == 'block') {
					$(this).css('-moz-transform', 'none');
					$(this).css('transform', 'none');
				}
				else {
					$(this).css('-moz-transform', 'rotate(-90deg)');
					$(this).css('transform', 'rotate(-90deg)');
				}
			}
			else {
				$(this).css('background-image', 'url(' + foldericon + ')');
			}
		}
	);
}

function setupFeedList() {
	var list = $('.collapsable,.feed').hover(
		function() {
			$(this).find('.feeds_delete,.feeds_edit').css('display', 'inline');
			$(this).find('.unreaditemcounter').css('display', 'none');
		},
		function() {
			$(this).find('.feeds_delete,.feeds_edit').css('display', 'none');
			$(this).find('.unreaditemcounter').css('display', 'inline');
		}
	);
	list.find('.feeds_delete').hide();
	list.find('.feeds_edit').hide();
	list.find('.unreaditemcounter').show();

	$('.feed').click(function() {
		News.Feed.load($(this).attr('data-id'));
	});

	// select initially loaded feed
	var loadedfeed = $('div.rightcontent').attr('data-id');
	$('li.feed[data-id="' + loadedfeed + '"]').attr('id', 'selected_feed');

	transformCollapsableTrigger();
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
			OC.appSettings({appid:'news', loadJS:true, cache:false});
		} catch(e) {
			alert(e);
		}
	});

	setupFeedList();

	News.Feed.updateAll();
	var updateInterval = 200000; //how often the feeds should update (in msec)
	setInterval('News.Feed.updateAll()', updateInterval);

	$('.title_unread').live('mouseenter', function(){
		var itemId = $(this).data('id');
        var feedId = $(this).data('feedid');
		News.Feed.markItem(itemId, feedId);
	});

});

$(document).click(function(event) {
	$('#feedfoldermenu').hide();
});
