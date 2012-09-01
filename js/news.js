News = {
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
						// FIXME: this should receive json by default
						var $folder = $(jsondata.data.listfolder);
						var title = $folder.children('.title').html();
						var id = $folder.data('id');
						var data = { 
							title: title
						};
						News.Objects.Menu.addNode(0, News.MenuNodeType.Folder, id, data);
						$('#addfolder_dialog').dialog('destroy').remove();
					} else {
						OC.dialogs.alert(jsondata.data.message, t('news', 'Error'));
					}
					$("#folder_add_name").val('');
					$(button).attr("disabled", false);
					$(button).prop('value', t('news','Add folder'));
			});
		},
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
			console.log(folderid);
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
							// FIXME: this should receive json by default
							var $feed = $(jsondata.data.listfeed);
							var title = $feed.children('.title').html();
							var icon = $feed.children('.title').css('background-image').replace(/"/g,"").replace(/url\(|\)$/ig, "");;
							console.log(icon);
							var unreadCount = $feed.children('.unread_items_count').html();
							var id = $feed.data('id');
							var data = { 
								title: title,
								unreadCount: unreadCount,
								icon: icon
							};
							News.Objects.Menu.addNode(folderid, News.MenuNodeType.Feed, id, data);
							News.Objects.Menu.load(News.MenuNodeType.Feed, jsondata.data.feedid);

							$('#addfeed_dialog').dialog('destroy').remove();
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
		
	},

}


