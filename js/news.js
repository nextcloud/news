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
		}
	},
	Feed: {
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
		}
	}
}

$(document).ready(function(){  
      
	$('#addfeedfolder').click(News.UI.overview);
	$('#addfeedfolder').keydown(News.UI.overview);
	
	$('.collapsable').click(function(){ 
		$(this).parent().children().toggle();
		$(this).toggle();
	});
	
	$('.accordion .title').click(function() {
		$(this).next().toggle();
		return false;
	}).next().hide();
 
});  