News={
	Settings:{
		cloudFileSelected:function(path){
// 				$.getJSON(OC.filePath('contacts', 'ajax', 'oc_photo.php'),{'path':path,'id':Contacts.UI.Card.id},function(jsondata){
// 					if(jsondata.status == 'success'){
// 						//alert(jsondata.data.page);
// 						Contacts.UI.Card.editPhoto(jsondata.data.id, jsondata.data.tmp)
// 						$('#edit_photo_dialog_img').html(jsondata.data.page);
// 					}
// 					else{
// 						OC.dialogs.alert(jsondata.data.message, t('contacts', 'Error'));
// 					}
// 				});
		}		  
	}
}
$(document).ready(function(){

	$('#opml_file').click(function() {
		OC.dialogs.filepicker(t('news', 'Select file'), News.Settings.cloudFileSelected, false, '', true);
	});
	


});


