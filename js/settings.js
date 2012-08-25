News = News || {}
News.Settings={
	importkind: '',
	importpath: '',
	IMPORTCLOUD:'cloud',
	IMPORTLOCAL:'local',
	cloudFileSelected:function(path){
		$.getJSON(OC.filePath('news', 'ajax', 'selectfromcloud.php'),{'path':path},function(jsondata){
			if(jsondata.status == 'success'){
				$('#browsebtn, #cloudbtn, #importbtn').show();
				$('#opml_file').text(t('news', 'File ') + path + t('news', ' loaded from cloud.'));
				News.Settings.importkind = News.Settings.IMPORTCLOUD;
				News.Settings.importpath = jsondata.data.tmp;
			}
			else{
				OC.dialogs.alert(jsondata.data.message, t('news', 'Error'));
			}
		});
	},
	browseFile:function(filelist){
		if(!filelist) {
			OC.dialogs.alert(t('news','No files selected.'), t('news', 'Error'));
			return;
		}
		var file = filelist[0];
		$('#browsebtn, #cloudbtn, #importbtn').show();
		$('#opml_file').text(t('news', 'File ') + file.name + t('news', ' loaded from local filesystem.'));
		$('#opml_file').prop('value', file.name);
	},
	importOpml:function(button){
		$(button).attr("disabled", true);
		$(button).prop('value', t('news', 'Importing...'));

		var path = '';
		if (News.Settings.importkind == News.Settings.IMPORTCLOUD) {
			path = News.Settings.importpath;
		} else if (this.importkind == this.IMPORTLOCAL) {
		}
		else {
			OC.dialogs.alert(t('news','Import kind not specified'), t('news', 'Error'));
		}

		$.post(OC.filePath('news', 'ajax', 'importopml.php'), { path: path }, function(jsondata){
			if (jsondata.status == 'success') {
				var message = jsondata.data.countsuccess + t('news', ' out of ') + jsondata.data.count + 
					t('news', ' feeds imported successfully from ') + jsondata.data.title;   
				OC.dialogs.alert(message, t('news', 'Success'));
			} else {
				OC.dialogs.alert(jsondata.data.message, t('news', 'Error'));
			}
			$(button).prop('value', t('news', 'Import'));
			$(button).attr("disabled", false);
		});
	},
	exportOpml:function(button){
		$(button).attr("disabled", true);
		$(button).prop('value', t('news', 'Downloading...'));
		document.location.href = OC.linkTo('news', 'opmlexporter.php');
		$(button).prop('value', t('news', 'Download'));
		$(button).attr("disabled", false);
	}
}

$('#browsebtn, #cloudbtn, #importbtn').hide();

$('#cloudbtn, #cloudlink').click(function() {
	/*
	  * it needs to be filtered by MIME type, but there are too many MIME types corresponding to opml
	  * and filepicker doesn't support multiple MIME types filter.
	  */
	OC.dialogs.filepicker(t('news', 'Select file'), News.Settings.cloudFileSelected, false, '', true);
});

$('#browsebtn, #browselink').click(function() {
	$('#file_upload_start').trigger('click');
});

$('#file_upload_start').change(function() {
	News.Settings.browseFile(this.files);
});

$('#importbtn').click(function() {
	News.Settings.importOpml(this);
});

$('#exportbtn').click(function() {
	News.Settings.exportOpml(this);
});
