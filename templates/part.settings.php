<div 
	class="bottom_settings"
	ng-controller="SettingsController"
	hide-settings-when-focus-lost>

	<div class="open_add bottom_popup" 
		ng-show="addIsShown()"
		ng-class="{expanded: addIsShown()}">
		<fieldset class="personalblock">
			<legend><strong><?php p($l->t('Add Subscription')); ?></strong></legend>
			<form>
				<select name="folder" 
						title="<?php p($l->t('Folder under which the feed is being saved')); ?>"
						ng-model="folderId"
						ng-disabled="isAddingFeed()"
						ng-options="folder.name for folder in getFolders()">
					<option value=""><?php p($l->t('-- choose folder --')); ?></option>
				</select>
				<p class="error">
					<span ng-show="feedEmptyError"><?php p($l->t('Address must not be empty!')); ?></span>
					<span ng-show="feedExistsError"><?php p($l->t('Feed exists already!')); ?></span>
					<span ng-show="feedError"><?php p($l->t('Could not add feed!')); ?></span>
				</p>
				<input type="text" 
					ng-model="feedUrl" 
					placeholder="<?php p($l->t('Address')); ?>" 
					ng-disabled="isAddingFeed()">
				<button title="<?php p($l->t('Add')); ?>" 
						ng-class="{loading: isAddingFeed()}"
						ng-click="addFeed(feedUrl, folderId)"><?php p($l->t('Add')); ?></button>
			</form>	
		</fieldset>
		<fieldset class="personalblock">
			<legend><strong><?php p($l->t('Add Folder')); ?></strong></legend>
			<form name="addFolderForm">
				<p class="error">
					<span ng-show="folderEmptyError"><?php p($l->t('Folder name must not be empty!')); ?></span>
					<span ng-show="folderExistsError"><?php p($l->t('Folder exists already!')); ?></span>
				</p>
				<input type="text" 
						ng-model="folderName" 
						ng-disabled="isAddingFolder()"
						name="folderName"
						maxlength="30"
						placeholder="<?php p($l->t('Folder Name')); ?>">
				<button title="<?php p($l->t('Add')); ?>" 
						ng-click="addFolder(folderName)"
						ng-class="{loading: isAddingFolder()}"><?php p($l->t('Add')); ?></button>
			</form>
		</fieldset>
	</div>


	<div id="feed_settings" class="bottom_popup"
		ng-class="{expanded: settingsAreShown()}">
		<ul class="controls">
			<li class="view show_all" 
				ng-show="getShowAll()"
				ng-click="setShowAll(false)"
				title="<?php p($l->t('Show everything')); ?>">
				<button></button>
			</li>
			<li class="view show_unread" 
				ng-show="!getShowAll()"
				ng-click="setShowAll(true)"
				title="<?php p($l->t('Show only unread')); ?>">
				<button></button>
			</li>
			<li style="float: right"
				ng-class="{active: settingsAreShown()}">
				<button id="settingsbtn" 
						title="<?php p($l->t('Settings')); ?>"
						ng-click="toggleSettings()">
					<img class="svg" 
						 src="<?php print_unescaped(image_path('core','actions/settings.png')); ?>" 
						 alt="<?php p($l->t('Settings')); ?>"   />
				</button>
			</li>
			<li style="float: right"
				title="<?php p($l->t('Add feed or folder')) ?>"
				ng-class="{active: addIsShown()}">
				<button ng-click="toggleAdd()">
					<img class="svg" 
						 src="<?php print_unescaped(link_to('news', 'img/add.svg')) ?>" 
						 alt="<?php p($l->t('Add Feed/Folder')) ?>" /></button>
			</li>
		</ul>

		<div class="open_settings" ng-show="settingsAreShown()">
			<fieldset class="personalblock">
				<legend><strong><?php p($l->t('Import / Export')); ?></strong></legend>
				<input type="file" id="file_upload_start" name="files[]" read-file/>
				<button title="<?php p($l->t('Import OPML')); ?>"
						id="browselink">
					<?php p($l->t('Import OPML')); ?>
				</button>
				<a class="button" 
					href="<?php print_unescaped(\OC_Helper::linkToRoute('news_export_opml')) ?>"
					title="<?php p($l->t('Export OPML')); ?>"><?php p($l->t('Export OPML')); ?></a>
			</fieldset>
			<fieldset class="personalblock">
				<legend><strong><?php p($l->t('Subscribelet')); ?></strong></legend>
				<p><?php print_unescaped($this->inc('part.subscribelet'));?>
				</p>
			</fieldset>
		</div>
	</div>
</div>