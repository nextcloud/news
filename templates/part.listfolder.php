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
	news-droppable>
	<button class="collapse"
			ng-hide="folder.editing"
			title="<?php p($l->t('Collapse'));?>"
			ng-click="folderBusinessLayer.toggleFolder(folder.id)"></button>
	<div ng-show="folder.editing" class="rename-feed">
        <input type="text" ng-model="folder.name" class="folder-input" autofocus>
        <button title="<?php p($l->t('Cancel')); ?>"
			ng-click="folderBusinessLayer.cancel(folder.id)"
			class="action-button back-button action"></button>
		<button title="<?php p($l->t('Save')); ?>"
			ng-click="folderBusinessLayer.rename(folder.id, folder.name)"
			class="action-button create-button action">
	  </button>
    </div>
	<a href="#"
	   class="title folder-icon"
	   ng-hide="folder.editing"
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
				ng-hide="folder.editing || !folder.id"
				class="svg action delete-icon delete-button"
				title="<?php p($l->t('Delete folder')); ?>"
				oc-tooltip></button>

		<span class="unread-counter"
			ng-show="folderBusinessLayer.getUnreadCount(folder.id) > 0 && !folder.editing">
			{{ unreadCountFormatter(folderBusinessLayer.getUnreadCount(folder.id)) }}
		</span>

		<button class="svg action mark-read-icon"
				ng-show="folderBusinessLayer.getUnreadCount(folder.id) > 0 && folder.id && !folder.editing"
				ng-click="folderBusinessLayer.markRead(folder.id)"
				title="<?php p($l->t('Mark read')); ?>"
				oc-tooltip></button>

		<button class="svg action delete-icon"
			ng-click="folderBusinessLayer.markErrorRead(folder.name)"
			title="<?php p($l->t('Delete folder')); ?>"
			ng-show="folder.error"
			oc-tooltip></button>

		<button class="svg action rename-feed-icon"
	                ng-hide="folder.editing"
			ng-click="folderBusinessLayer.edit(folder.id)"
			title="<?php p($l->t('Rename folder')); ?>"
	                oc-tooltip></button>

	</span>
	<ul>
		<?php print_unescaped($this->inc('part.listfeed', ['folderId' => 'folder.id'])); ?>
	</ul>

	<div class="message" ng-show="folder.error">{{ folder.error }}</div>
</li>
