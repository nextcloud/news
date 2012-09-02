News = {
	DropDownMenu: {
		fade:function(menu){
			$(menu).toggle();
			return false;
		},
		dropdown:function(button){
			var list = $(button).parent().find('ul.dropdownmenu');
			if (list.css('display') == 'none')
				list.slideDown('fast').show();
			else
				list.slideUp('fast');

			return false;
		},
		selectItem:function(item, folderid){
			var parent = $(item).parent().parent();
			parent.find('.dropdownBtn').text($(item).text());
			parent.find(':input[name="folderid"]').val(folderid);
			parent.find('ul.dropdownmenu').slideUp('fast');
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

			var folderid = 0;

			var url;
			url = OC.filePath('news', 'ajax', 'createfolder.php');

			$.post(url, { name: displayname, parentid: folderid },
				function(jsondata){
					if(jsondata.status == 'success'){
						News.Objects.Menu.addNode(0, jsondata.data.listfolder);
						$('#addfolder_dialog').dialog('close');
					} else {
						OC.dialogs.alert(jsondata.data.message, t('news', 'Error'));
					}
					$("#folder_add_name").val('');
					$(button).attr("disabled", false);
					$(button).prop('value', t('news','Add folder'));
			});
		},
		changeName:function(button){
			var folderName = $("#changefolder_dialog input[type=text]").val().trim();
			var folderId = parseInt($('#changefolder_dialog input[type=hidden]').val().trim());

			if(folderName.length == 0) {
				OC.dialogs.alert(t('news', 'Name of the folder cannot be empty.'), t('news', 'Error'));
				return false;
			}

			$(button).attr("disabled", true);
			$(button).prop('value', t('news', 'Changing...'));

			var	url = OC.filePath('news', 'ajax', 'changefoldername.php');
			var data = { 
				folderName: folderName, 
				folderId: folderId 
			};

			$.post(url, data, function(jsonData){
				if(jsonData.status == 'success'){
					folderName = $('<div>').text(folderName).html();
					News.Objects.Menu.updateNode(News.MenuNodeType.Folder, folderId, {title: folderName});
					$('#changefolder_dialog').dialog('close');
				} else {
					OC.dialogs.alert(jsonData.data.message, t('news', 'Error'));
				}
				$(button).attr("disabled", false);
				$(button).prop('value', t('news','Change folder name'));
			});
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
				folderid = $('#addfeed_dialog .inputfolderid').val();
			}

			$.ajax({
				type: "POST",
				url: OC.filePath('news', 'ajax', 'createfeed.php'),
				data: { 'feedurl': feedurl, 'folderid': folderid },
				dataType: "json",
				success: function(jsonData){
					if($('#firstrun').length > 0){
						window.location.reload(); 
					} else {
						if(jsonData.status == 'success'){		
							News.Objects.Menu.addNode(folderid, jsonData.data.listfeed);
							News.Objects.Menu.load(News.MenuNodeType.Feed, jsonData.data.feedid);
							$('#addfeed_dialog').dialog('close');
						} else {
							OC.dialogs.alert(jsonData.data.message, t('news', 'Error'));
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


