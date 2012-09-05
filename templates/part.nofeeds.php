<div id="appsettings" class="popup bottomleft hidden"></div>
<div id="firstrun">
	<h1><?php echo $l->t("You don't have any feed in your reader.") ?></h1>
	<div id="selections">
		<fieldset id="addfeed_dialog_firstrun">
		<legend style="margin-left:10px;"><img src="<?php echo OCP\Util::imagePath('news','rss.svg'); ?>"> <?php echo $l->t('Add feed') ?></legend>
			<input type="text" id="feed_add_url" placeholder="<?php echo $l->t('Address'); ?>" />
			<input type="submit" value="<?php echo $l->t('Subscribe'); ?>" onclick="News.Feed.submit(this)" id="feed_add_submit" />
		</fieldset>
		<br />
		<fieldset id="importopml_dialog_firstrun">
		<legend style="margin-left:10px"><img src="<?php echo OCP\Util::imagePath('news','opml-icon.svg'); ?>"> <?php echo $l->t('Import OPML') ?></legend>
			<button class="svg" id="browsebtn_firstrun" title="<?php echo $l->t('Upload file from desktop'); ?>" onclick="News.DropDownMenu.fade('ul#feedfoldermenu')"><img class="svg" src="<?php echo OCP\Util::imagePath('core','actions/upload.svg'); ?>" alt="<?php echo $l->t('Upload'); ?>"   /></button>
			<button class="svg" id="cloudbtn_firstrun" title="<?php echo $l->t('Select file from ownCloud'); ?>"><img class="svg" src="<?php echo OCP\Util::imagePath('core','actions/upload.svg'); ?>" alt="<?php echo $l->t('Select'); ?>"   /></button>
			<span id="opml_file">
			<?php echo $l->t('Select file from') . ' '; ?><a href='#' class="settings" id="browselink"><?php echo $l->t('local filesystem');?></a>
			<?php echo $l->t(' or '); ?><a href='#' class="settings" id="cloudlink"><?php echo $l->t('cloud');?></a>.
			</span>
			<input type="file" id="file_upload_start" name="files[]" />
			<input style="float: right" id="importbtn_firstrun" type="submit" value="<?php echo $l->t('Import');?>" />
		</fieldset>
	</div>
	<div>
	<?php
	require_once OC_App::getAppPath('news') .'/templates/subscribelet.php';
	echo '<h1>' . $l->t('Or...') . '</h1>';
	?>
	<?php createSubscribelet(); ?>
	</div>
</div>
