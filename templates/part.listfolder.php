<li ng-class="{
	active: isFeedActive(feedType.Folder, folder.id), 
	open: folder.open,
	collapsable: folder.hasChildren,
	all_read: getUnreadCount(feedType.Folder, folder.id)==0
}" 
    ng-repeat="folder in folders"
    ng-show="folder.show"
    class="folder"
    data-id="{{folder.id}}"
    droppable>
    <button class="collapsable_trigger" 
            title="<?php p($l->t('Collapse'));?>"
            ng-click="toggleFolder(folder.id)"></button>
	<a href="#" 
	   class="title"
	   ng-click="loadFeed(feedType.Folder, folder.id)">
	   {{folder.name}}
	</a>
	<span class="unread_items_counter">
		{{ getUnreadCount(feedType.Folder, folder.id) }}
	</span>
	<span class="buttons">
		<button ng-click="delete(feedType.Folder, folder.id)"
		        class="svg action feeds_delete" 
		        title="<?php p($l->t('Delete folder')); ?>"></button>
		<button class="svg action feeds_edit" 
				ng-click="renameFolder(folder.id)"
		        title="<?php p($l->t('Rename folder')); ?>"></button>
		<button class="svg action feeds_markread" 
		        ng-click="markAllRead(feedType.Folder, folder.id)"
		        title="<?php p($l->t('Mark all read')); ?>"></button>
	</span>
	<ul>
		<?php print_unescaped($this->inc('part.listfeed', array('folderId' => 'folder.id'))); ?>
	</ul>
</li>