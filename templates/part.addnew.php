<li class="add-new">
	<a class="list-title list-title-with-icon"
		data-apps-slide-toggle=".add-new-popup"
		href="#"
		oc-click-focus="{
			selector: '.add-new-popup input[ng-model=feedUrl]'
		}"
 	>+ <span><?php p($l->t('Add Website'))?></span></a>

	<div class="add-new-popup">

		<fieldset class="personalblock">
			<p class="error" ng-show="feedExistsError || folderExistsError">
				<span ng-show="feedExistsError">
					<?php p($l->t('Error: address exists already!')); ?>
				</span>
				<span ng-show="folderExistsError">
					<?php p($l->t('Error: folder exists already')); ?>
				</span>
			</p>
			<form>

				<input type="text"
					ng-model="feedUrl"
					placeholder="<?php p($l->t('Address')); ?>"
					name="adress"
					autofocus>
				<button title="<?php p($l->t('Add')); ?>"
						class="primary"
						ng-disabled="!feedUrl.trim()"
						ng-click="addFeed(feedUrl, folderId.id)"><?php p($l->t('Add')); ?></button>
			</form>
			<form>
				<select name="folder"
						data-create="<?php p($l->t('New folder')); ?>"
						title="<?php p($l->t('Folder')); ?>"
						ng-model="folderId"
						ng-options="folder.name for folder in folderBusinessLayer.getAll()"
						ng-hide="addNewFolder">
					<option value="" selected="selected"><?php p($l->t('Choose folder')); ?></option>
				</select>
				<button title="<?php p($l->t('New folder')); ?>"
						ng-click="addNewFolder=true"
						ng-hide="addNewFolder"
						class="action-button new-button action"
						oc-click-focus="{selector: 'input[name=\'foldername\']'}"></button>
				<input type="text"
						ng-model="folderName"
						ng-show="addNewFolder"
						name="foldername"
						placeholder="<?php p($l->t('Folder name')); ?>"
						autofocus
						class="folder-input"
						ui-keyup="{13: 'addFolder(folderName)'}"/>
				<button title="<?php p($l->t('Back to folder selection')); ?>"
						ng-show="addNewFolder"
						ng-click="addNewFolder=false"
						class="action-button back-button action"></button>
				<button title="<?php p($l->t('Create folder')); ?>"
						ng-show="addNewFolder"
						ng-click="addFolder(folderName)"
						ng-disabled="!folderName.trim()"
						ng-class="{loading: isAddingFolder()}"
						class="action-button create-button action">
				</button>
			</form>
		</fieldset>
	</div>
</li>
