
<?php
	function print_folder(OC_News_Folder $folder, $depth){
		echo '<li class="dropdown" onclick="News.DropDownMenu.selectItem(this, ' . $folder->getId() . ')">' . strtoupper($folder->getName()) . '</li>';
		$children = $folder->getChildren();
		foreach($children as $child) {
			if ($child instanceOf OC_News_Folder){
				print_folder($child, $depth+1);
			}
		}
	}
?>

<div id="addfeedfolder_dialog" title="<?php echo $l->t("Add Feed/Folder"); ?>">
<table width="100%" style="border: 0;">
<tr>
	<td>Add new feed</td>
	<td>
		<div class="add_parentfolder">
			<button id="dropdownBtn" onclick="News.DropDownMenu.show(this)">
			    <?php echo $l->t('ALL FEEDS'); ?>
			</button>
			<input type="hidden" name="folderid" value="0" />
			<ul class="dropdown">
				<?php print_folder($_['allfeeds'], 0); ?>
			</ul>
		</div>
	</td>
</tr>
<tr>
	<td><input type="text" id="feed_add_url" placeholder="<?php echo $l->t('URL'); ?>" class="news_input" /></td>
	<td><input type="submit" value="<?php echo $l->t('Add feed'); ?>" onclick="News.Feed.submit(this)" id="feed_add_submit" /></td>
</tr>
	<td>Add new folder</td>
	<td>
		<div class="add_parentfolder">
			<button id="dropdownBtn" onclick="News.DropDownMenu.show(this)">
			    <?php echo $l->t('ALL FEEDS'); ?>
			</button>
			<input type="hidden" name="folderid" value="0" />
			<ul class="dropdown">
				<?php print_folder($_['allfeeds'], 0); ?>
			</ul>
		</div>
	</td>
</tr>
<tr>
	<td><input type="text" id="folder_add_name" placeholder="<?php echo $l->t('Folder name'); ?>" class="news_input" /></td>
	<td><input type="submit" value="<?php echo $l->t('Add folder'); ?>" onclick="News.Folder.submit(this)" id="folder_add_submit" /></td>
</tr>
</table>