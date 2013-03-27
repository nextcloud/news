<fieldset class="personalblock">
	<legend><strong><?php p($l->t('Import / Export OPML')); ?></strong></legend>
	<input type="file" id="opml-upload" name="files[]" read-file/>
	<button title="<?php p($l->t('Import')); ?>" 
		oc-forward-click="{selector:'#opml-upload'}">
		<?php p($l->t('Import')); ?>
	</button>
	<button ng-disabled="feeds.length==0" title="<?php p($l->t('Export')); ?>"
		ng-click="export()">
		<?php p($l->t('Export')); ?>
	</button>
</fieldset>
<fieldset class="personalblock">
	<legend><strong><?php p($l->t('Subscribelet')); ?></strong></legend>
	<p><?php print_unescaped($this->inc('part.subscribelet'));?>
	</p>
</fieldset>
