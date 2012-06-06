<div id="leftcontent" class="leftcontent">
	<ul id="feeds">
		<?php echo $this->inc("test"); ?>
	</ul>
</div>
<div id="bottomcontrols">
	<form>
		<button class="svg" id="add" title="<?php echo $l->t('Add Feed/Folder'); ?>"><img class="svg" src="<?php echo OCP\Util::linkTo('news', 'img/add.svg'); ?>" alt="<?php echo $l->t('Add Feed/Folder'); ?>"   /></button>
	</form>
</div>
<div id="rightcontent" class="rightcontent">
	<?php ?>
</div>

