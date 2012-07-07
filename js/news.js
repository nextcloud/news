News={
	UI:{
		overview:function(){
			if($('#addfeedfolder_dialog').dialog('isOpen') == true){
				$('#addfeedfolder_dialog').dialog('moveToTop');
			}else{
				$('#dialog_holder').load(OC.filePath('news', 'ajax', 'addfeedfolder.php'), function(jsondata){
					if(jsondata.status != 'error'){
						$('#addfeedfolder_dialog').dialog({
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
					OC.dialogs.alert(t('news', 'Displayname cannot be empty.'), t('news', 'Error'));
					return false;
				}
				
				var url;
				url = OC.filePath('news', 'ajax', 'createfolder.php');
				
				$.post(url, { name: displayname },
					function(jsondata){
						if(jsondata.status == 'success'){
							//$(button).closest('tr').prev().html(jsondata.page).show().next().remove();
							OC.dialogs.alert(jsondata.data.message, t('news', 'Success!'));
						} else {
							OC.dialogs.alert(jsondata.data.message, t('news', 'Error'));
						}
				});
		},
		delete:function(folderid) {
			$('#feeds_delete').tipsy('hide');
			OC.dialogs.confirm(t('news', 'Are you sure you want to delete this folder and all its feeds?'), t('news', 'Warning'), function(answer) {
				if(answer == true) {
					$.post(OC.filePath('news', 'ajax', 'deletefolder.php'),{'folderid':folderid},function(jsondata){
						if(jsondata.status == 'success'){
							alert('removed!');
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
				
				var url;
				url = OC.filePath('news', 'ajax', 'newfeed.php');
				
				$.post(url, { feedurl: feedurl },
					function(jsondata){
						if(jsondata.status == 'success'){
							//$(button).closest('tr').prev().html(jsondata.page).show().next().remove();
							OC.dialogs.alert(jsondata.data.message, t('news', 'Success!'));
						} else {
							OC.dialogs.alert(jsondata.data.message, t('news', 'Error'));
						}
				});
		},
		delete:function(feedid) {
			$('#feeds_delete').tipsy('hide');
			OC.dialogs.confirm(t('news', 'Are you sure you want to delete this feed?'), t('news', 'Warning'), function(answer) {
				if(answer == true) {
					$.post(OC.filePath('news', 'ajax', 'deletefeed.php'),{'feedid':feedid},function(jsondata){
						if(jsondata.status == 'success'){
							alert('removed!');
						}
						else{
							OC.dialogs.alert(jsondata.data.message, t('news', 'Error'));
						}
					});
				}
			});
			return false;
		},
		markItem:function(itemid) {
			$.post(OC.filePath('news', 'ajax', 'markitem.php'),{'itemid':itemid},function(jsondata){
				if(jsondata.status == 'success'){
					
				}
				else{
					OC.dialogs.alert(jsondata.data.message, t('news', 'Error'));
				}
			});
		}
	}
}

$(document).ready(function(){  
      
	$('#addfeedfolder').click(News.UI.overview);
	
	$('.collapsable').click(function(){ 
		$(this).parent().children().toggle();
		$(this).toggle();
	});
	
	$('.accordion .title_unread').click(function() {
		$(this).next().toggle();
		return false;
	}).next().hide();

	$('.accordion .title_read').click(function() {
		$(this).next().toggle();
		return false;
	}).next().hide();

	
	$('.feeds_list').hover(function() {
		$(this).find('#feeds_delete').toggle();
		return false;
	}).find('#feeds_delete').hide();
});  