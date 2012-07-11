<div id="leftcontent" class="leftcontent">
	<ul id="feeds">
		<?php echo $this->inc("part.feeds"); ?>
	</ul>
</div>

<div id="bottomcontrols">
<!-- 	<form> -->
	<ul class="controls">
		<li>
			<button class="svg" id="addfeedfolder" title="<?php echo $l->t('Add Feed/Folder'); ?>" onclick="News.DropDownMenu.fade(this)"><img class="svg" src="<?php echo OCP\Util::linkTo('news', 'img/add.svg'); ?>" alt="<?php echo $l->t('Add Feed/Folder'); ?>"   /></button>
			<ul class="menu" id="fademenu">
				<li class="menuItem" id="addfeed"><?php echo $l->t('Add Feed'); ?></li>
				<li class="menuItem" id="addfolder"><?php echo $l->t('Add Folder'); ?></li>
			</ul>
		</li>
		<li><button class="sv" title="<?php echo $l->t('Change View'); ?>">Eye</button></li>
		<li><button class="svg" title="<?php echo $l->t('Settings'); ?>">Settings</button></li>
	<ul>
<!-- 	</form> -->
</div>

<div id="rightcontent" class="rightcontent" data-id="<?php echo $_['feedid']; ?>">
	<?php
		if ($_['feedid']){
			echo $this->inc("part.items");
		}
		else {
			echo $this->inc("part.nofeeds");
		}
	?>
</div>

<!-- Dialogs -->
<div id="dialog_holder"></div>
<!-- End of Dialogs -->

