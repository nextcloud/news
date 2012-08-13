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
		setAllItemsRead:function(feedId) {
			$.post(OC.filePath('news', 'ajax', 'setallitemsread.php'), { 'feedId' : feedId }, function(jsonData) {
				if(jsonData.status == 'success'){
					// mark ui items as read
					$("#feed_items .feed_item:not(.read)").each(function(){
						$(this).addClass('read');
					});

					var $feedItemCounter = $('li.feed[data-id="'+feedId+'"]').find('.unreaditemcounter');
					$feedItemCounter.removeClass('nonzero').addClass('zero');
					$feedItemCounter.empty();
				} else {
					OC.dialogs.alert(t('news', 'Error while loading the feed'), t('news', 'Error'));
				}
			});
		},
		load:function(feedId) {
			$.post(OC.filePath('news', 'ajax', 'loadfeed.php'), { 'feedId' : feedId }, function(jsonData) {
				if(jsonData.status == 'success'){
					var $rightContent = $(".rightcontent");
					$rightContent.attr('data-id', feedId);
					var $feedItems = $('#feed_items');
					$feedItems.empty();
					$feedItems.html(jsonData.data.feedItems);
					var $feedTitle = $(".feed_controls .feed_title h1");
					$feedTitle.html('» ' + jsonData.data.feedTitle);

					$('li#selected_feed').attr('id', '');
					$('li.feed[data-id="' + feedId + '"]').attr('id', 'selected_feed');

					transformCollapsableTrigger();
					bindItemEventListeners();
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
		}, 
		filter:function(value){
			// TODO: safe this on the server
			switch(value){
				case 'all':
					$("#feed_items .feed_item").show();
					break;
				case 'newest':
					$("#feed_items .feed_item.read").hide();
					break;
				default:
					break;
			}
			
		}
	},
	// this handler handles changes in the ui when the itemstatus changes
	ItemStatusHandler: function(itemId){
		var _itemId = itemId;
		var _$currentItem = $('#feed_items li[data-id="' + itemId + '"]');
		var _$currentItemStar = _$currentItem.children('.utils').children('.primary_item_utils').children('.star');
		var _$currentItemKeepUnread = _$currentItem.children('.utils').children('.secondary_item_utils').children('.keep_unread').children('input[type=checkbox]');
		var _feedId = _$currentItem.data('feedid');
		var _read = _$currentItem.hasClass('read');
		var _important = _$currentItemStar.hasClass('important');
		var _keepUnread = _$currentItemKeepUnread.prop('checked');

		/**
		 * Switches important items to unimportant and vice versa
		 */
		var _toggleImportant = function(){
			if(_important){
				status = 'unimportant';
			} else {
				status = 'important';
			}

			var data = {
				itemId: itemId,
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
		 * Toggles an item as "keep unread". This prevents all handlers to mark it as unread
		 * except the current one
		 */
		var _toggleKeepUnread = function(){
			if(_keepUnread){
				_$currentItemKeepUnread.prop("checked", false);
			} else {
				_$currentItemKeepUnread.prop("checked", true);
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
			if(read === _read) return;
			// check if the keep unread flag was set
			if(read && _keepUnread) return;

			if(read){
				status = 'read';
			} else {
				status = 'unread';
			}

			var data = {
				itemId: itemId,
				status: status
			};

			$.post(OC.filePath('news', 'ajax', 'setitemstatus.php'), data, function(jsonData){
				if(jsonData.status == 'success'){
					var counterplace = $('li.feed[data-id="'+_feedId+'"]').find('.unreaditemcounter');
					var title = $('li.feed[data-id="'+_feedId+'"] > a');
					var oldcount = counterplace.html();
					counterplace.empty();

					if(read){
						_$currentItem.addClass('read');
						if (oldcount <= 1) {
							counterplace.removeClass('nonzero').addClass('zero');
							title.removeClass('nonzero').addClass('zero');
						} else {
							counterplace.html(--oldcount);
						}
					} else {
						_$currentItem.removeClass('read');
						if (oldcount === '') {
							counterplace.removeClass('zero').addClass('nonzero');
							title.removeClass('zero').addClass('nonzero');
							counterplace.html(1);
						} else {
							counterplace.html(++oldcount);
						}
					}

				} else {
					OC.dialogs.alert(jsonData.data.message, t('news', 'Error'));
				}
			})
			
		};

		// set public methods
		this.setRead = function(read){ _setRead(read); }
		this.isRead = function(){ return _read; }
		this.toggleImportant = function(){ _toggleImportant(); }
		this.toggleKeepUnread = function(){ _toggleKeepUnread(); }
	},

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
	})

	// mark or unmark as important
	$('#feed_items li.star').click(function(){
		var $item = $(this).parent().parent().parent('.feed_item');
		var itemId = $item.data('id');
        var handler = new News.ItemStatusHandler(itemId);
		handler.toggleImportant();
	})

	// toggle logic for the keep unread handler
	$('#feed_items .keep_unread').click(function(){
		var $item = $(this).parent().parent().parent('.feed_item');
		var itemId = $item.data('id');
        var handler = new News.ItemStatusHandler(itemId);
		handler.toggleKeepUnread();
	})

	// bind the mark all as read button
	$('#mark_all_as_read').click(function(){
		var feedId = $('.rightcontent').data('id');
		News.Feed.setAllItemsRead(feedId);
	});

}


$(document).ready(function(){
	$('#addfeed, #addfeedbtn').click(function() {
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

	bindItemEventListeners();

	// filter for newest or all items
	$('#feed_filter').change(function(){
		News.Feed.filter($(this).val());
	});

	// mark items whose title was hid under the top edge as read
	// when the bottom is reached, mark all items as read
	$('#feed_items').scroll(function(){
		var boxHeight = $(this).height();
		var scrollHeight = $(this).prop('scrollHeight');
		var scrolled = $(this).scrollTop() + boxHeight;

		$(this).children('ul').children('.feed_item:not(.read)').each(function(){
			var itemOffset = $(this).position().top;
			if(itemOffset <= 0 || scrolled >= scrollHeight){
				var itemId = $(this).data('id');
				var handler = new News.ItemStatusHandler(itemId);
				handler.setRead(true);
			}
		})
	});

});

$(document).click(function(event) {
	$('#feedfoldermenu').hide();
});
