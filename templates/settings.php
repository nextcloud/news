<dl>
	<dt><?php echo $l->t('Import feeds'); ?></dt>
	<dd><span id="opml_file">
	    <?php echo $l->t('Select file from <a href="#" class="settings" id="browselink">local filesystem</a> or <a href="#" class="settings" id="cloudlink">cloud</a>'); ?>
	    </span>
	    <input type="file" id="file_upload_start" name="files[]" />
	</dd>
	<dt><?php echo $l->t('Export feeds'); ?></dt>
	<dd>
	    <button id="exportbtn" title="<?php echo $l->t('Download OPML'); ?>">Download OPML</button>
	</dd>
	<dt><?php echo $l->t('Subscribelet'); ?></dt>
	<dd>
	    <?php
		require_once OC_App::getAppPath('news') .'/templates/subscribelet.php';
		createSubscribelet();
	    ?>
	</dd>
</dl>