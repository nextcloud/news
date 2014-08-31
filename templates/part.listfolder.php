<li ng-class="{
        active: Navigation.isFolderActive(folder.id),
        open: folder.opened && Navigation.hasFeeds(folder.id),
        collapsible: Navigation.hasFeeds(folder.id),
        unread: Navigation.getFolderUnreadCount(folder.id) != 0,
        failed: folder.error
    }"
    ng-repeat="folder in Navigation.getAllFolders() | orderBy:'id':true"
    ng-show="Navigation.getFolderUnreadCount(folder.id) != 0
            || Navigation.isShowAll()
            || Navigation.isFolderActive(folder.id)
            || Navigation.subFeedActive(folder.id)
            || !folder.id"
    class="folder has-counter"
    data-id="{{ folder.id }}"
    news-droppable>
    <button class="collapse"
            ng-hide="folder.editing"
            title="<?php p($l->t('Collapse'));?>"
            ng-click="Navigation.toggleFolder(folder.name)"></button>
    <div ng-if="folder.editing" class="rename-feed">
        <input type="text" ng-model="folder.name" class="folder-input" autofocus>
        <button title="<?php p($l->t('Cancel')); ?>"
            ng-click="Navigation.cancelRenameFolder(folder.id)"
            class="action-button back-button action"></button>
        <button title="<?php p($l->t('Save')); ?>"
            ng-click="Navigation.renameFolder(folder.id, folder.name)"
            class="action-button create-button action">
      </button>
    </div>

    <a ng-href="#/items/folders/{{ folder.id }}/"
        class="title folder-icon"
        ng-hide="folder.editing"
        ng-class="{
            'progress-icon': !folder.id,
            'problem-icon': folder.error
        }">
       {{ folder.name }}
    </a>

    <span class="utils">


        <span class="unread-counter"
            ng-show="Navigation.getUnreadCount(folder.id) > 0 && !folder.editing">
            {{ Navigation.getFolderUnreadCount(folder.id) | unreadCountFormatter }}
        </span>

        <!--
        <button ng-click="Navigation.delete(folder.id)"
                ng-hide="folder.editing || !folder.id"
                class="svg action delete-icon delete-button"
                title="<?php p($l->t('Delete folder')); ?>"
                oc-tooltip></button>

        <button class="svg action mark-read-icon"
                ng-show="Navigation.getUnreadCount(folder.id) > 0 && folder.id && !folder.editing"
                ng-click="Navigation.markRead(folder.id)"
                title="<?php p($l->t('Mark read')); ?>"
                oc-tooltip></button>

        <button class="svg action delete-icon"
            ng-click="Navigation.markErrorRead(folder.name)"
            title="<?php p($l->t('Delete folder')); ?>"
            ng-show="folder.error"
            oc-tooltip></button>

        <button class="svg action rename-feed-icon"
                    ng-hide="folder.editing"
            ng-click="Navigation.edit(folder.id)"
            title="<?php p($l->t('Rename folder')); ?>"
                    oc-tooltip></button>
        -->
    </span>
    <ul>
        <?php print_unescaped($this->inc('part.listfeed', ['folderId' => 'folder.id'])); ?>
    </ul>

    <div class="message" ng-show="folder.error">{{ folder.error }}</div>
</li>
