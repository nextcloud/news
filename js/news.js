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
						// if we got a parent folder
						if(folderid > 0){
							$('.collapsable_container[data-id="' + folderid + '"] > ul').append(jsondata.data.listfolder);
						} else {
							$('#feeds > ul').append(jsondata.data.listfolder);
						}
						setupFeedList();
						$('#addfolder_dialog').dialog('destroy').remove();
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
			
			var folderid = 0;
			if($('#firstrun').length == 0){
				folderid = $('#inputfolderid:input[name="folderid"]').val();
			}

			$.ajax({
				type: "POST",
				url: OC.filePath('news', 'ajax', 'createfeed.php'),
				data: { 'feedurl': feedurl, 'folderid': folderid },
				dataType: "json",
				success: function(jsondata){
					if($('#firstrun').length > 0){
						window.location.reload(); 
					} else {
						if(jsondata.status == 'success'){
							if(folderid > 0){
								$('.collapsable_container[data-id="' + folderid + '"] > ul').append(jsondata.data.listfeed);	
							} else {
								$('#feeds > ul').append(jsondata.data.listfeed);
							}
							setupFeedList();
							News.Feed.load(jsondata.data.feedid);

							//$('#ui-dialog-title-addfeed_dialog').html('Feed added. Do you want to add another feed?')

							/*
							OC.dialogs.confirm(t('news', ), t('news', 'Feed added!'), function(answer) {
								if(!answer) {
									$('#addfeed_dialog').dialog('destroy').remove();
									$('ul.accordion').before(jsondata.data.part_newfeed);
								}
							});*/
						} else {
							OC.dialogs.alert(jsondata.data.message, t('news', 'Error'));
						}
						$("#feed_add_url").val('');
						$(button).attr("disabled", false);
						$(button).prop('value', t('news', 'Add feed'));
					}
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
								// window.location.reload();
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
		load:function(feedId) {
			var $feedItems = $('#feed_items');
			$feedItems.empty();
			$feedItems.addClass('loading');
			$.post(OC.filePath('news', 'ajax', 'loadfeed.php'), { 'feedId' : feedId }, function(jsonData) {
				if(jsonData.status == 'success'){
					// set active id
					var $rightContent = $(".rightcontent");
					$rightContent.attr('data-id', feedId);
					News.Feed.activeFeedId = parseInt(feedId);
					// load in new items
					$feedItems.html(jsonData.data.feedItems);
					// scroll to the top position
					$feedItems.scrollTop(0);
					// set title
					var $feedTitle = $(".feed_controls .feed_title h1");
					$feedTitle.html(jsonData.data.feedTitle);
					$feedTitle.attr('title', jsonData.data.feedTitle);
					// update unread count
					$feedHandler = new News.FeedStatusHandler(feedId);
					$feedHandler.setUnreadCount(jsonData.data.unreadItemCount);
					// select new feed
					$('li#selected_feed').attr('id', '');
					if(feedId < 0){
						$('li[data-id="' + feedId + '"]').attr('id', 'selected_feed');
					} else {
						$('li.feed[data-id="' + feedId + '"]').attr('id', 'selected_feed');
					}
					// refresh callbacks
					transformCollapsableTrigger();
					bindItemEventListeners();
				}
				else {
					OC.dialogs.alert(t('news', 'Error while loading the feed'), t('news', 'Error'));
				}
				$feedItems.removeClass('loading');
			});
		},


		// this array is used to store ids to prevent sending too
		// many posts when scrolling. the structure is: feed_id: boolean
		processing:{},
		activeFeedId: -1000,
	},



}


