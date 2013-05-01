<li class="add-new">
	<a class="list-title list-title-with-icon"
		oc-click-slide-toggle="{
			selector: '.add-new-popup',
			hideOnFocusLost: true,
			cssClass: 'opened'
		}" 
		href="#"
		oc-click-focus="{
			selector: '.add-new-popup input[ng-model=feedUrl]'
		}">+ </a>
	<div class="add-new-popup">
		<fieldset class="personalblock">
			<p class="error">
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
					ng-disabled="isAddingFeed() || isAddingFolder()"
					name="adress"
					autofocus>
				<button title="<?php p($l->t('Add')); ?>" 
						class="primary"
						ng-disabled="isAddingFeed() || isAddingFolder() || !feedUrl.trim()"
						ng-click="addFeed(feedUrl, folderId.id)"><?php p($l->t('Add')); ?></button>
			</form>
			<form>
				<select name="folder" 
						data-create="<?php p($l->t('New folder')); ?>"
						title="<?php p($l->t('Folder')); ?>"		
						ng-model="folderId"
						ng-disabled="isAddingFolder()"
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
						ng-disabled="isAddingFolder()"
						ng-show="addNewFolder"
						name="foldername"
						placeholder="<?php p($l->t('Folder name')); ?>" 
						autofocus
						ui-keyup="{13: 'addFolder(folderName)'}"/>
				<button title="<?php p($l->t('Back to folder selection')); ?>" 
						ng-show="addNewFolder"
						ng-click="addNewFolder=false"
						ng-disabled="isAddingFolder()"
						class="action-button back-button action"></button>
				<button title="<?php p($l->t('Create folder')); ?>" 
						ng-show="addNewFolder"
						ng-click="addFolder(folderName)"
						ng-disabled="isAddingFolder() || !folderName.trim()"
						ng-class="{loading: isAddingFolder()}"
						class="action-button create-button action">
				</button>
			</form>
		</fieldset>
	</div>
</li>
