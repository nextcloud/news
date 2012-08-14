<div id="appsettings" class="popup bottomleft hidden"></div>
<div id="firstrun">
	<?php echo $l->t('You have no feeds in your reader.') ?>
	<div id="selections">
		<input type="button" id="addfeedbtn" value="<?php echo $l->t('Add feed') ?>" /><br />
		<input type="button" id="importopmlbtn" value="<?php echo $l->t('Import OPML') ?>" />
	</div>
	<div>
	<?php
	require_once(OC_App::getAppPath('news') .'/templates/bookmarklet.php');
	echo $l->t('Or... ');
	?>
	<div>
	<?php createBookmarklet(); ?>
	</div>
	</div>
</div>