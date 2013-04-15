<li ng-class="{
		active: folderBl.isActive(folder.id), 
		open: folder.opened && folderBl.hasFeeds(folder.id),
		collapsible: folderBl.hasFeeds(folder.id),
		unread: folderBl.getUnreadCount(folder.id) != 0,
		failed: folder.error
	}" 
	ng-repeat="folder in folderBl.getAll()"
	ng-show="folderBl.isVisible(folder.id) || !folder.id"
	class="folder"
	data-id="{{ folder.id }}"
	droppable>
	<button class="collapse" 
			title="<?php p($l->t('Collapse'));?>"
			ng-click="folderBl.toggleFolder(folder.id)"></button>
	<a href="#" 
	   class="title folder-icon"
	   ng-click="folderBl.load(folder.id)"
	   ng-class="{
			'progress-icon': !folder.id,
			'problem-icon': folder.error
		}">
	   {{ folder.name }}
	</a>

	<span class="utils">

		<button ng-click="folderBl.delete(folder.id)"
				ng-hide="folderBl.hasFeeds(folder.id) || !folder.id"
				class="svg action delete-icon" 
				title="<?php p($l->t('Delete folder')); ?>"></button>

		<span class="unread-counter">
			{{ folderBl.getUnreadCount(folder.id) }}
		</span>
		
		<button class="svg action mark-read-icon" 
				ng-show="folderBl.getUnreadCount(folder.id) > 0 && folder.id"
				ng-click="folderBl.markFolderRead(folder.id)"
				title="<?php p($l->t('Mark all read')); ?>"></button>

		<button class="svg action mark-read-icon"
			ng-click="folderBl.markErrorRead(folder.name)"
			title="<?php p($l->t('Discard')); ?>"
			ng-show="folder.error"></button>

<!--		<button class="svg action edit-icon" 
				ng-click="renameFolder(folder.id)"
				title="<?php p($l->t('Rename folder')); ?>"></button>
-->

	</span>
	<ul>
		<?php print_unescaped($this->inc('part.listfeed', array('folderId' => 'folder.id'))); ?>
	</ul>
	
	<div class="message" ng-show="folder.error">{{ folder.error }}</div>
</li>
