NewsSettings={
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
		},
		cloudFileSelected:function(path){
// 			$.getJSON(OC.filePath('contacts', 'ajax', 'oc_photo.php'),{'path':path,'id':Contacts.UI.Card.id},function(jsondata){
// 				if(jsondata.status == 'success'){
// 					//alert(jsondata.data.page);
// 					Contacts.UI.Card.editPhoto(jsondata.data.id, jsondata.data.tmp)
// 					$('#edit_photo_dialog_img').html(jsondata.data.page);
// 				}
// 				else{
// 					OC.dialogs.alert(jsondata.data.message, t('contacts', 'Error'));
// 				}
// 			});
		}
	},
}
$(document).ready(function(){
  
	$('#settings').click(function() {
		NewsSettings.UI.overview('#import_dialog', 'importdialog.php');
	});
	
	//OC.dialogs.filepicker(t('news', 'Select file'), NewsSettings.cloudFileSelected, false, '', true);
});


