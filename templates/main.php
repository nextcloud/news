<!-- Dialogs -->
<div id="dialog_holder"></div>
<!-- End of Dialogs -->

<?php
if ($_['feedid']){

echo $this->inc("part.items.header");

?>

<div id="leftcontent" class="leftcontent">
	<div id="feeds">
		<ul>
			<li data-id="-2" class="subscriptions folder"><a href="#" ><?php echo $l->t('Subscriptions'); ?></a></li>
			<li data-id="-1" class="starred folder"><a href="#" ><?php echo $l->t('Starred'); ?></a></li>
			<?php echo $this->inc("part.feeds"); ?>
		</ul>
	</div>
</div>

<div id="feed_settings">
	<ul class="controls">
		<li id="addfeedfolder" title="<?php echo $l->t('Add feed or folder'); ?>">
			<button class="svg"><img class="svg" src="<?php echo OCP\Util::linkTo('news', 'img/add.svg'); ?>" alt="<?php echo $l->t('Add Feed/Folder'); ?>"   /></button>
			<ul class="menu" id="feedfoldermenu">
				<li id="addfeed"><?php echo $l->t('Feed'); ?></li>
				<li id="addfolder"><?php echo $l->t('Folder'); ?></li>
			</ul>
		</li>
		<li style="float: right">
			<button class="svg" id="settingsbtn" title="<?php echo $l->t('Settings'); ?>"><img class="svg" src="<?php echo OCP\Util::imagePath('core','actions/settings.png'); ?>" alt="<?php echo $l->t('Settings'); ?>"   /></button>
		</li>
	</ul>
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
