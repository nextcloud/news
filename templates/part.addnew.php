<li class="add-new">
	<a class="list-title list-title-with-icon"
		oc-click-slide-toggle="{
			selector: '.add-new-popup',
			hideOnFocusLost: true,
			cssClass: 'opened'
		}" 
		href="#"
		click-focus="{
			selector: '.add-new-popup input[ng-model=feedUrl]'
		}">
		<?php p($l->t('Add Website'))?>
	</a>

	<div class="add-new-popup" >
		<fieldset class="personalblock">
			<p class="error">
				<span ng-show="feedEmptyError"><?php p($l->t('Address must not be empty!')); ?></span>
				<span ng-show="feedError">
					<?php p($l->t('Could not add feed! Check if feed contains valid RSS or exists already!')); ?>
				</span>
				<span ng-show="folderExistsError"><?php p($l->t('Folder exists already')); ?></span>
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
						ng-class="{loading: isAddingFeed()}"
						ng-disabled="isAddingFeed() || isAddingFolder()"
						ng-click="addFeed(feedUrl, folderId.id)"><?php p($l->t('Add')); ?></button>

				<select name="folder" 
						data-create="<?php p($l->t('New folder')); ?>"
						title="<?php p($l->t('Folder')); ?>"						
						ng-model="folderId"
						ng-disabled="isAddingFolder()"
						ng-options="folder.name for folder in folders"
						ng-hide="addNewFolder">
					<option value="" selected="selected"><?php p($l->t('Choose folder')); ?></option>
				</select>
				<button title="<?php p($l->t('Add')); ?>" 
						ng-click="addNewFolder=true"
						ng-hide="addNewFolder"><?php p($l->t('New')); ?></button>
				<input type="text" 
						ng-model="folderName" 
						ng-disabled="isAddingFolder()"
						ng-show="addNewFolder"
						name="foldername"
						placeholder="<?php p($l->t('Folder name')); ?>" 
						autofocus
						ui-keyup="{13: 'addFolder(folderName)'}"/>
					<button title="<?php p($l->t('Add')); ?>" 
						ng-show="addNewFolder"
						ng-click="addFolder(folderName)"
						ng-disabled="isAddingFolder()"
						ng-class="{loading: isAddingFolder()}">
						<?php p($l->t('Create')); ?>
					</button>
			</form>	
		</fieldset>
	</div>
</li>
