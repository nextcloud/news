News = News || {}

News.Settings={
	cloudFileSelected:function(path){
		$.getJSON(OC.filePath('news', 'ajax', 'selectfromcloud.php'),{'path':path},function(jsondata){
			if(jsondata.status == 'success'){
				News.Settings.importOpml(jsondata.data.tmp);
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
		$.ajax({
			url: OC.filePath('news', 'ajax', 'importopml.php'),
			data: formData,
			cache: false,
			contentType: false,
			processData: false,
			type: 'POST',
			success: function(jsondata){
				if (jsondata.status == 'success') {
					var message = jsondata.data.countsuccess + t('news', ' out of ') + jsondata.data.count +
					t('news', ' feeds imported successfully from ') + jsondata.data.title;
					OC.dialogs.alert(message, t('news', 'Success'));
				}
				else {
					OC.dialogs.alert(jsondata.data.message, t('news', 'Error'));
				}
			}
		    });
	},
	importOpml:function(path){
		$.post(OC.filePath('news', 'ajax', 'importopml.php'), { path: path }, function(jsondata){
			if (jsondata.status == 'success') {
				var message = jsondata.data.countsuccess + t('news', ' out of ') + jsondata.data.count +
					t('news', ' feeds imported successfully from ') + jsondata.data.title;
				OC.dialogs.alert(message, t('news', 'Success'));
			} else {
				OC.dialogs.alert(jsondata.data.message, t('news', 'Error'));
			}
		});
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
