<div id="leftcontent" class="leftcontent">
	<ul id="feeds">
		<?php echo $this->inc("part.feeds"); ?>
	</ul>
</div>

<div id="bottomcontrols">
<div id="addmenu"> 
	<ul>
	<li>
	<form>
		<button class="svg" id="add" title="<?php echo $l->t('Add Feed/Folder'); ?>"><img class="svg" src="<?php echo OCP\Util::linkTo('news', 'img/add.svg'); ?>" alt="<?php echo $l->t('Add Feed/Folder'); ?>"   /></button>
	</form>
	<ul>
	<li><a href="p1.html">Feed</a></li>
	<li><a href="p2.hmtl">Folder</a></li>
	</li>
	</ul>
</div>
</div>

<div id="rightcontent" class="rightcontent">
	<?php ?>
</div>

