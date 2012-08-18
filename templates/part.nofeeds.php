<div id="appsettings" class="popup bottomleft hidden"></div>
<div id="firstrun">
	<h1><?php echo $l->t("You don't have any feed in your reader.") ?></h1>
	<div id="selections">
		<div id="addfeed_dialog_firstrun">
		<input type="text" id="feed_add_url" placeholder="<?php echo $l->t('Address'); ?>" />
		<input type="submit" value="<?php echo $l->t('Add feed'); ?>" onclick="News.Feed.submit(this)" id="feed_add_submit" />
		</div>
		<input type="button" id="addfeedbtn" value="<?php echo $l->t('Add feed') ?>" /><br />
		<input type="button" id="importopmlbtn" value="<?php echo $l->t('Import OPML') ?>" />
	</div>
	<div>
	<?php
	require_once(OC_App::getAppPath('news') .'/templates/subscribelet.php');
	echo '<h1>' . $l->t('Or...') . '</h1>';
	?>
	<?php createSubscribelet(); ?>
	</div>
</div>
