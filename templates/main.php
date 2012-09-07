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

<div id="rightcontent" class="rightcontent">
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

	echo $this->inc("part.dialogues");
