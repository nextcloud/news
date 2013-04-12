<div id="app-settings-header">
<button name="app settings" 
		class="settings-button"
		oc-click-slide-toggle="{
			selector: '#app-settings-content',
			hideOnFocusLost: true,
			cssClass: 'opened'
		}"></button>
</div>

<div id="app-settings-content">
	<fieldset class="personalblock">
		<legend><strong><?php p($l->t('Import / Export OPML')); ?></strong></legend>
		<input type="file" id="opml-upload" name="import" 
				oc-read-file="import($fileContent)"/>
		<button title="<?php p($l->t('Import')); ?>" 
			oc-forward-click="{selector:'#opml-upload'}">
			<?php p($l->t('Import')); ?>
		</button>
		<a title="<?php p($l->t('Export')); ?>" class="button"
			href="<?php p(\OCP\Util::linkToRoute('news_export_opml')); ?>" 
			target="_blank"
			ng-show="feedBl.getNumberOfFeeds() > 0">
			<?php p($l->t('Export')); ?>
		</a>
		<button
			title="<?php p($l->t('Export')); ?>" 
			ng-hide="feedBl.getNumberOfFeeds() > 0" disabled>
			<?php p($l->t('Export')); ?>
		</button>

	</fieldset>
	<fieldset class="personalblock">
		<legend><strong><?php p($l->t('Subscribelet')); ?></strong></legend>
		<p><?php print_unescaped($this->inc('part.subscribelet'));?>
		</p>
	</fieldset>
</div>