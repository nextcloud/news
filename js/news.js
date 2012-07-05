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
		},
		doDelete:function() {
			$('#feeds_delete').tipsy('hide');
			OC.dialogs.confirm(t('news', 'Are you sure you want to delete this feed?'), t('news', 'Warning'), function(answer) {
				if(answer == true) {
					$.post(OC.filePath('contacts', 'ajax', 'deletefeed.php'),{'id':Contacts.UI.Card.id},function(jsondata){
						if(jsondata.status == 'success'){
							var newid = '';
							var curlistitem = $('#leftcontent [data-id="'+jsondata.data.id+'"]');
							var newlistitem = curlistitem.prev();
							if(newlistitem == undefined) {
								newlistitem = curlistitem.next();
							}
							curlistitem.remove();
							if(newlistitem != undefined) {
								newid = newlistitem.data('id');
							}
							$('#rightcontent').data('id',newid);
							this.id = this.fn = this.fullname = this.shortname = this.famname = this.givname = this.addname = this.honpre = this.honsuf = '';
							this.data = undefined;
							
							if($('#contacts li').length > 0) { // Load first in list.
								Contacts.UI.Card.update(newid);
							} else {
								// load intro page
								$.getJSON(OC.filePath('contacts', 'ajax', 'loadintro.php'),{},function(jsondata){
									if(jsondata.status == 'success'){
										id = '';
										$('#rightcontent').data('id','');
										$('#rightcontent').html(jsondata.data.page);
									}
									else{
										OC.dialogs.alert(jsondata.data.message, t('contacts', 'Error'));
									}
								});
							}
						}
						else{
							OC.dialogs.alert(jsondata.data.message, t('contacts', 'Error'));
						}
					});
				}
			});
			return false;
		}
	}
}

$(document).ready(function(){  
      
	$('#addfeedfolder').click(News.UI.overview);
	
	$('.collapsable').click(function(){ 
		$(this).parent().children().toggle();
		$(this).toggle();
	});
	
	$('.accordion .title').click(function() {
		$(this).next().toggle();
		return false;
	}).next().hide();
	
	$('#feeds_delete').click( function() { News.Feed.doDelete(); return false;} );

});  