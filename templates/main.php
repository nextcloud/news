<!-- Dialogs -->
<div id="dialog_holder"></div>
<!-- End of Dialogs -->

<?php
if ($_['feedid']){

$showAll = OCP\Config::getUserValue(OCP\USER::getUser(), 'news', 'showAll'); 

if($showAll){
	$viewButtonImg = 'eye_all.svg';
	$viewButtonTitle = $l->t('Show everything');
	$viewButtonClass = 'show_all';
} else {
	$viewButtonImg = 'eye_unread.svg';
	$viewButtonTitle = $l->t('Show only unread');
	$viewButtonClass = 'show_unread';
}

?>

<div id="leftcontent_news" class="leftcontent_news">
	<div id="feed_wrapper">
		<div id="feeds">
			<ul data-id="0">
				<?php echo $this->inc("part.feeds"); ?>
			</ul>
		</div>
	</div>
	<div id="feed_settings">
		<ul class="controls">
			<li id="addfeedfolder" title="<?php echo $l->t('Add feed or folder'); ?>">
				<button><img class="svg" src="<?php echo OCP\Util::linkTo('news', 'img/add.svg'); ?>" alt="<?php echo $l->t('Add Feed/Folder'); ?>" /></button>
				<ul class="menu" id="feedfoldermenu">
					<li id="addfeed"><?php echo $l->t('Feed'); ?></li>
					<li id="addfolder"><?php echo $l->t('Folder'); ?></li>
				</ul>
			</li>
			<li id="view" title="<?php echo $viewButtonTitle; ?>" class="<?php echo $viewButtonClass; ?>">
				<button></button>
			</li>
			<li style="float: right">
				<button id="settingsbtn" title="<?php echo $l->t('Settings'); ?>"><img class="svg" src="<?php echo OCP\Util::imagePath('core','actions/settings.png'); ?>" alt="<?php echo $l->t('Settings'); ?>"   /></button>
			</li>
		</ul>
	</div>
</div>

<div id="rightcontent" class="rightcontent" data-id="<?php echo $_['feedid']; ?>">
	<?php
			echo '<div id="feed_items">';
				echo $this->inc("part.items");
			echo '</div>';
	?>

	<div id="appsettings" class="popup bottomleft hidden"></div>

</div>

<?php
	} else {
		echo $this->inc("part.nofeeds");
	}
?>

<div id="addfolder_dialog" title="<?php echo $l->t("Add Folder"); ?>">
	<table width="100%" style="border: 0;">
	<tr>
		<td>Add new folder</td>
		<td></td>
	</tr>
	<tr>
		<td><input type="text" id="folder_add_name" placeholder="<?php echo $l->t('Folder name'); ?>" class="news_input" /></td>
		<td><input type="submit" value="<?php echo $l->t('Add folder'); ?>" id="folder_add_submit" /></td>
	</tr>
	</table>
</div>


<div id="addfeed_dialog" title="<?php echo $l->t("Add Subscription"); ?>">
	<table width="100%" style="border: 0;">
	<tr>
		<td>Add new feed</td>
		<td>
			<div class="add_parentfolder">
				<button class="dropdownBtn">
				    <?php echo $l->t('Choose folder'); ?>
				</button>
				<input class="inputfolderid" type="hidden" name="folderid" value="0" />
				<ul class="menu dropdownmenu">
				</ul>
			</div>
		</td>
	</tr>
	<tr>
		<td><input type="text" id="feed_add_url" placeholder="<?php echo $l->t('Address'); ?>" class="news_input" /></td>
		<td><input type="submit" value="<?php echo $l->t('Add'); ?>" id="feed_add_submit" /></td>
	</tr>
	</table>
</div>