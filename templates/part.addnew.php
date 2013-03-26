<li class="add-new">
	<a class="list-title list-title-with-icon"
			oc-click-slide-toggle="{
				selector: '.add-new-popup',
				hideOnFocusLost: true
			}" href="#">
		<?php p($l->t('New'))?>
	</a>

	<div class="add-new-popup">
		<fieldset class="personalblock">
			<legend><strong><?php p($l->t('Add Subscription')); ?></strong></legend>
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
					ng-disabled="isAddingFeed() || isAddingFolder()">
				<button title="<?php p($l->t('Add')); ?>" 
						ng-class="{loading: isAddingFeed()}"
						ng-disabled="adding"
						ng-click="addFeed(feedUrl, folderId)"><?php p($l->t('Add')); ?></button>
				<select name="folder" 
						data-create="<?php p($l->t('New folder')); ?>"
						title="<?php p($l->t('Folder')); ?>"
						ng-model="folderId"
						ng-disabled="isAddingFolder()"
						ng-options="folder.name for folder in getFolders()"
						add-folder-select>
					<option value="" selected><?php p($l->t('No folder')); ?></option>
				</select>
			</form>	
		</fieldset>
	</div>
</li>
