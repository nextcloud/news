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
	}
}

$(document).ready(function(){  
      
	$('#addfeedfolder').click(News.UI.overview);
	$('#addfeedfolder').keydown(News.UI.overview);
	
	$('#feed_add_submit').click(addBookmark);
  
});  