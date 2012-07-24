<div id="import_dialog" title="<?php echo $l->t("Settings"); ?>">
<dl>
	<dt><?php echo $l->t('Import'); ?></dt>
	<dd><button class="svg" title="<?php echo $l->t('Upload file from desktop'); ?>" onclick="News.DropDownMenu.fade('ul#feedfoldermenu')"><img class="svg" src="<?php echo OCP\Util::imagePath('core','actions/upload.svg'); ?>" alt="<?php echo $l->t('Upload'); ?>"   /></button>
	    <button class="svg" id="cloudbtn" title="<?php echo $l->t('Select file from ownCloud'); ?>"><img class="svg" src="<?php echo OCP\Util::imagePath('core','actions/upload.svg'); ?>" alt="<?php echo $l->t('Select'); ?>"   /></button>
	    <input type="text" name="opml_file" id="opml_file" placeholder="<?php echo $l->t('.opml file');?>" />
	    <input type="submit" value="<?php echo $l->t('Import feeds');?>" /></dd>
	<dt><?php echo $l->t('Export'); ?></dt>
	<dd></dd>
</dl> 