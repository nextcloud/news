<li ng-class="{
		active: folderBusinessLayer.isActive(folder.id), 
		open: folder.opened && folderBusinessLayer.hasFeeds(folder.id),
		collapsible: folderBusinessLayer.hasFeeds(folder.id),
		unread: folderBusinessLayer.getUnreadCount(folder.id) != 0,
		failed: folder.error
	}" 
	ng-repeat="folder in folderBusinessLayer.getAll() | orderBy:'id':true"
	ng-show="folderBusinessLayer.isVisible(folder.id) || !folder.id"
	class="folder"
	data-id="{{ folder.id }}"
	droppable>
	<button class="collapse" 
			title="<?php p($l->t('Collapse'));?>"
			ng-click="folderBusinessLayer.toggleFolder(folder.id)"></button>
	<a href="#" 
	   class="title folder-icon"
	   ng-click="folderBusinessLayer.load(folder.id)"
	   ng-class="{
			'progress-icon': !folder.id,
			'problem-icon': folder.error
		}"
		oc-click-focus="{selector: '#app-content'}">
	   {{ folder.name }}
	</a>

	<span class="utils">

		<button ng-click="folderBusinessLayer.delete(folder.id)"
				ng-hide="folderBusinessLayer.hasFeeds(folder.id) || !folder.id"
				class="svg action delete-icon" 
				title="<?php p($l->t('Delete folder')); ?>"
				oc-tooltip></button>

		<span class="unread-counter"
			ng-show="folderBusinessLayer.getUnreadCount(folder.id) > 0">
			{{ unreadCountFormatter(folderBusinessLayer.getUnreadCount(folder.id)) }}
		</span>
		
		<button class="svg action mark-read-icon" 
				ng-show="folderBusinessLayer.getUnreadCount(folder.id) > 0 && folder.id"
				ng-click="folderBusinessLayer.markFolderRead(folder.id)"
				title="<?php p($l->t('Mark read')); ?>"
				oc-tooltip></button>

		<button class="svg action delete-icon"
			ng-click="folderBusinessLayer.markErrorRead(folder.name)"
			title="<?php p($l->t('Delete folder')); ?>"
			ng-show="folder.error"
			oc-tooltip></button>

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
