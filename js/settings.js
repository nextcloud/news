News = News || {}

News.Settings={
	cloudFileSelected:function(path){
		$.getJSON(OC.filePath('news', 'ajax', 'selectfromcloud.php'),{'path':path},function(jsondata){
			if(jsondata.status == 'success'){
				News.Settings.importOpml('fromCloud', jsondata.data.tmp);
			}
			else{
				OC.dialogs.alert(jsondata.data.message, t('news', 'Error'));
			}
		});
		$('#appsettings_popup').remove();
	},
	browseFile:function(filelist){
		if(!filelist) {
			OC.dialogs.alert(t('news','No files selected.'), t('news', 'Error'));
			return;
		}
		var file = filelist[0];
		//check file format/size/...
		var formData = new FormData();
		formData.append('file', file);
		
		News.Settings.importOpml('fromFile', formData);
		$('#appsettings_popup').remove();
	},
	importOpml:function(type, data){
	  
		$('#notification').fadeIn();
		$('#notification').html(t('news', 'Importing OPML file...'));
		
		if (type == 'fromCloud') {
			ajaxData = { path: data };
			settings = {};
		}
		else if (type == 'fromFile') {
			ajaxData = data;
			settings = { cache: false, contentType: false, processData: false };
		}
		else {
			throw t('news', 'Not a valid type');
		}
		
		param = {
			url: OC.filePath('news', 'ajax', 'uploadopml.php'),
			data: ajaxData,
			type: 'POST',
			success: function(jsondata){
				if (jsondata.status == 'success') {
					var eventSource=new OC.EventSource(OC.filePath('news','ajax','importopml.php'),{source:jsondata.data.source, path:jsondata.data.path});
					eventSource.listen('progress',function(progress){
						if (progress.data.type == 'feed') {
							News.Objects.Menu.addNode(progress.data.folderid, progress.data.listfeed);
						} else if (progress.data.type == 'folder') {
							News.Objects.Menu.addNode(0, progress.data.listfolder);
						}
					});
					eventSource.listen('success',function(data){
						$('#notification').html(t('news', 'Importing done'));
					});
					eventSource.listen('error',function(error){
						$('#notification').fadeOut('400');
						OC.dialogs.alert(error, t('news', 'Error while importing feeds.'));
					});
				}
				else {
					OC.dialogs.alert(jsondata.data.message, t('news', 'Error'));
				}
				$('#notification').delay('2500').fadeOut('400');
			}
		};
		
		$.ajax($.extend(param, settings));
	},
	exportOpml:function(button){
		document.location.href = OC.linkTo('news', 'opmlexporter.php');
		$('#appsettings_popup').remove();
	}
}


$('#cloudlink').click(function() {
	/*
	  * it needs to be filtered by MIME type, but there are too many MIME types corresponding to opml
	  * and filepicker doesn't support multiple MIME types filter.
	  */
	OC.dialogs.filepicker(t('news', 'Select file'), News.Settings.cloudFileSelected, false, '', true);
});

$('#browselink').click(function() {
	$('#file_upload_start').trigger('click');
});

$('#file_upload_start').change(function() {
	News.Settings.browseFile(this.files);
});

$('#exportbtn').click(function() {
	News.Settings.exportOpml(this);
});
