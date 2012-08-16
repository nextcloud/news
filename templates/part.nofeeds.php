<div id="appsettings" class="popup bottomleft hidden"></div>
<div id="firstrun">
	<h1><?php echo $l->t('You have no feeds in your reader.') ?></h1>
	<div id="selections">
		<div id="addfeed_dialog_firstrun">
		<table width="200px" style="border: 2px;">
		<tr>
			<td>Add new feed</td>
			<td>
				<div class="add_parentfolder">
					<button id="dropdownBtn" onclick="News.DropDownMenu.dropdown(this)">
					    <?php echo $l->t('Choose folder'); ?>
					</button>
					<input id="inputfolderid" type="hidden" name="folderid" value="0" />
					<ul class="menu" id="dropdownmenu">
						<?php echo $this->inc("part.folderlist"); ?>
					</ul>
				</div>
			</td>
		</tr>
		<tr>
			<td><input type="text" id="feed_add_url" placeholder="<?php echo $l->t('Link'); ?>" class="news_input" /></td>
			<td><input type="submit" value="<?php echo $l->t('Add'); ?>" onclick="News.Feed.submit(this)" id="feed_add_submit" /></td>
		</tr>
		</table>
		</div>
		<input type="button" id="addfeedbtn" value="<?php echo $l->t('Add feed') ?>" /><br />
		<input type="button" id="importopmlbtn" value="<?php echo $l->t('Import OPML') ?>" />
	</div>
	<div>
	<?php
	require_once(OC_App::getAppPath('news') .'/templates/bookmarklet.php');
	echo '<h1>' . $l->t('Or...') . '</h1>';
	?>
	<div>
	<?php createBookmarklet(); ?>
	</div>
	</div>
</div>