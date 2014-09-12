<li ng-class="{
        active: Navigation.isFolderActive(folder.id),
        open: folder.opened && Navigation.hasFeeds(folder.id),
        collapsible: Navigation.hasFeeds(folder.id),
        unread: Navigation.getFolderUnreadCount(folder.id) > 0
    }"
    ng-repeat="folder in Navigation.getFolders() | orderBy:'id':true"
    ng-show="Navigation.getFolderUnreadCount(folder.id) != 0
            || Navigation.isShowAll()
            || Navigation.isFolderActive(folder.id)
            || Navigation.subFeedActive(folder.id)
            || !folder.id"
    class="folder with-counter with-menu"
    data-id="{{ folder.id }}"
    news-droppable>
    <button class="collapse"
            ng-hide="folder.editing"
            title="<?php p($l->t('Collapse'));?>"
            ng-click="Navigation.toggleFolder(folder.name)"></button>

    <div ng-if="folder.deleted" class="app-navigation-entry-deleted" news-timeout="Navigation.removeFeed(feed)">
        <div class="app-navigation-entry-deleted-description"><?php p($l->t('Deleted')); ?> {{ feed.title }}</div>
        <button class="icon-history"
                title="<?php p($l->t('Undo')); ?>"
                ng-click="Navigation.undeleteFolder(folder)"></button>
        <button class="icon-close"
                title="<?php p($l->t('Remove notification')); ?>"
                ng-click="Navigation.removeFolder(folder)"></button>
    </div>

    <div ng-if="folder.editing" class="app-navigation-entry-edit">
        <input name="folderRename" type="text" value="{{ folder.name }}" news-auto-focus>
        <button title="<?php p($l->t('Rename')); ?>"
                ng-click="Navigation.renameFolder(folder)"
                class="action icon-checkmark">
        </button>
    </div>

    <a ng-href="#/items/folders/{{ folder.id }}/"
        class="title icon-folder"
        ng-hide="folder.editing || folder.error"
        ng-class="{
            'folder-loading': !folder.id
        }">
       {{ folder.name }}
    </a>

    <div class="app-navigation-entry-utils"
         ng-show="folder.id && !folder.editing && !folder.error && !folder.deleted">
        <ul>
            <li class="app-navigation-entry-utils-counter"
                ng-show="folder.id && Navigation.getFolderUnreadCount(folder.id) > 0">
                {{ Navigation.getFolderUnreadCount(folder.id) | unreadCountFormatter }}
            </li>
            <li class="app-navigation-entry-utils-menu-button">
                <button title="<?php p($l->t('Menu')); ?>"></button>
            </li>
        </ul>
    </div>

    <div class="app-navigation-entry-menu">
        <ul>
            <li><button ng-click="folder.editing=true"
                        class="icon-rename"
                        title="<?php p($l->t('Rename folder')); ?>"></button></li>
            <li><button ng-click="Navigation.deleteFolder(folder)"
                        class="icon-delete"
                        title="<?php p($l->t('Delete folder')); ?>"></button></li>
            <li><button ng-show="Navigation.getFolderUnreadCount(folder.id) > 0"
                        class="icon-checkmark"
                        ng-click="Navigation.markFolderRead(folder.id)"
                        title="<?php p($l->t('Read all')); ?>"></button></li>
        </ul>
    </div>
    <ul ng-hide="folder.error">
        <?php print_unescaped($this->inc('part.navigation.feed', ['folderId' => 'folder.id'])); ?>
    </ul>

    <div class="error-message" ng-show="folder.error">
        <h2 class="title">{{ folder.name }}</h2>
        <span class="message">{{ folder.error }}</span>
        <button type="button "
                title="<?php p($l->t('Dismiss')); ?>"
                ng-click="Navigation.removeFolder(folder)"></button>
    </div>
</li>
