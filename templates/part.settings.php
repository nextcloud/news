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
		<legend><strong><?php p($l->t('Subscriptions')); ?></strong></legend>
		
		<input type="file" id="opml-upload" name="import" accept="text/x-opml, text/xml"
				oc-read-file="import($fileContent)"/>
		<button title="<?php p($l->t('Import')); ?>" 
			class="upload-icon svg"
			oc-forward-click="{selector:'#opml-upload'}">
			<?php p($l->t('Import')); ?>
		</button>


		<a title="<?php p($l->t('Export')); ?>" class="button download-icon svg"
			href="<?php p(\OCP\Util::linkToRoute('news_export_opml')); ?>" 
			target="_blank"
			ng-show="feedBusinessLayer.getNumberOfFeeds() > 0">
			<?php p($l->t('Export')); ?>
		</a>
		<button
			class="download-icon svg"
			title="<?php p($l->t('Export')); ?>" 
			ng-hide="feedBusinessLayer.getNumberOfFeeds() > 0" disabled>
			<?php p($l->t('Export')); ?>
		</button>

		<p class="error" ng-show="error">
			<?php p($l->t('Error when importing: file does not contain valid OPML')); ?>
		</p>

	</fieldset>

	<fieldset class="personalblock">
		<legend><strong><?php p($l->t('Unread/Starred Articles')); ?></strong></legend>
		<input type="file" id="google-upload" name="importgoogle" 
			accept="application/json"
			oc-read-file="importGoogleReader($fileContent)"/>
		<button title="<?php p($l->t('Import')); ?>" 
			class="upload-icon svg"
			oc-forward-click="{selector:'#google-upload'}">
			<?php p($l->t('Import')); ?>
		</button>

		<a title="<?php p($l->t('Export')); ?>" class="button download-icon svg"
			href="<?php p(\OCP\Util::linkToRoute('news_export_articles')); ?>" 
			target="_blank"
			ng-show="feedBusinessLayer.getNumberOfFeeds() > 0">
			<?php p($l->t('Export')); ?>
		</a>
		<button
			class="download-icon svg"
			title="<?php p($l->t('Export')); ?>" 
			ng-hide="feedBusinessLayer.getNumberOfFeeds() > 0" disabled>
			<?php p($l->t('Export')); ?>
		</button>

		<p class="error" ng-show="jsonError">
			<?php p($l->t('Error when importing: file does not contain valid JSON')); ?>
		</p>

	</fieldset>
</div>