<dl>
	<dt><?php echo $l->t('Import feeds'); ?></dt>
	<dd><button class="svg" id="browsebtn" title="<?php echo $l->t('Upload file from desktop'); ?>" onclick="News.DropDownMenu.fade('ul#feedfoldermenu')"><img class="svg" src="<?php echo OCP\Util::imagePath('core','actions/upload.svg'); ?>" alt="<?php echo $l->t('Upload'); ?>"   /></button>
	    <button class="svg" id="cloudbtn" title="<?php echo $l->t('Select file from ownCloud'); ?>"><img class="svg" src="<?php echo OCP\Util::imagePath('core','actions/upload.svg'); ?>" alt="<?php echo $l->t('Select'); ?>"   /></button>
	    <span id="opml_file">
	    <?php echo $l->t('No file selected. Select file from '); ?><a href='#' id="browselink"><?php echo $l->t('local filesystem');?></a>
	    <?php echo $l->t(' or '); ?><a href='#' id="cloudlink"><?php echo $l->t('cloud');?></a>.
	    </span>
	    <input type="file" id="file_upload_start" name="files[]" />
	    <input style="float: right" id="importbtn" type="submit" value="<?php echo $l->t('Import');?>" /></dd>
	<dt><?php echo $l->t('Export feeds'); ?></dt>
	<dd></dd>
</dl>