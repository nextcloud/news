<!-- Dialogs -->
<div id="dialog_holder"></div>
<!-- End of Dialogs -->

<?php
if ($_['feedid']){
?>

<div id="leftcontent" class="leftcontent">
	<ul id="feeds">
		<?php echo $this->inc("part.feeds"); ?>
	</ul>
</div>

<div id="feedcontrols">
	<ul class="controls">
		<li>
			<button class="svg" id="addfeedfolder" title="<?php echo $l->t('Add Feed/Folder'); ?>" onclick="News.DropDownMenu.fade('ul#feedfoldermenu')"><img class="svg" src="<?php echo OCP\Util::linkTo('news', 'img/add.svg'); ?>" alt="<?php echo $l->t('Add Feed/Folder'); ?>"   /></button>
		</li>
		<li>
			<button class="svg" title="<?php echo $l->t('Change View'); ?>">Eye</button>
		</li>
		<li style="float: right">
			<button class="svg" id="settingsbtn" title="<?php echo $l->t('Settings'); ?>"><img class="svg" src="<?php echo OCP\Util::imagePath('core','actions/settings.png'); ?>" alt="<?php echo $l->t('Settings'); ?>"   /></button>
		</li>
	</ul>
</div>

<ul class="menu" id="feedfoldermenu">
	<li class="menuItem" id="addfeed"><?php echo $l->t('Feed'); ?></li>
	<li class="menuItem" id="addfolder"><?php echo $l->t('Folder'); ?></li>
</ul>


<div id="rightcontent" class="rightcontent" data-id="<?php echo $_['feedid']; ?>">
	<?php
			echo $this->inc("part.items.header");
			echo $this->inc("part.items");
	?>

	<div id="appsettings" class="popup bottomleft hidden"></div>

</div>

<?php
	} else {
		echo $this->inc("part.nofeeds");
	}
?>
