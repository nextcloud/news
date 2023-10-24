<li ng-class="{
        active: Navigation.isFolderActive(folder.id),
        open: folder.opened || folder.getsFeed,
        unread: Navigation.isFolderUnread(folder.id),
        deleted: folder.deleted,
        editing: folder.editing
    }"
    ng-repeat="folder in Navigation.getFolders() | orderBy:'name.toLowerCase()':false:localeComparator"
    ng-show="Navigation.isFolderUnread(folder.id)
            || Navigation.isShowAll()
            || Navigation.isFolderActive(folder.id)
            || Navigation.subFeedActive(folder.id)
            || !folder.id
            || folder.getsFeed
            || !Navigation.hasFeeds(folder.id)"
    class="folder with-counter with-menu animate-show collapsible"
    data-id="{{ folder.id }}"
    news-droppable>
    <button class="collapse"
            ng-hide="folder.editing || folder.deleted"
            title="<?php p($l->t('Collapse'));?>"
            ng-click="Navigation.toggleFolder(folder.name)"></button>

    <a ng-href="#/items/folders/{{ folder.id }}/"
        class="title icon-folder"
        ng-if="!folder.error && folder.id">
       {{ folder.name }}
    </a>

    <a class="title icon-loading-small" ng-if="!(folder.id || folder.error)">
       {{ folder.name }}
    </a>

    <div ng-if="folder.deleted"
        class="app-navigation-entry-deleted"
        news-timeout="Navigation.deleteFolder(folder)">
        <div class="app-navigation-entry-deleted-description">
            <?php p($l->t('Deleted folder')); ?>: {{ folder.name }}
        </div>
        <button class="icon-history app-navigation-entry-deleted-button"
                title="<?php p($l->t('Undo delete folder')); ?>"
                ng-click="Navigation.undoDeleteFolder(folder)"></button>
    </div>

    <div ng-if="folder.editing" class="app-navigation-entry-edit"
        ng-class="{
            'folder-rename-error':
                folder.renameError ||
                (folderName != folder.name &&
                !Navigation.renamingFolder &&
                Navigation.folderNameExists(folderName))
            }">
        <form ng-submit="Navigation.renameFolder(folder, folderName)">
            <input name="folderName"
                type="text"
                ng-init="folderName=folder.name"
                ng-class="{
                    'ng-invalid':
                        folderName != folder.name &&
                        !Navigation.renamingFolder &&
                        Navigation.folderNameExists(folderName)
                }"
                ng-model="folderName"
                ng-model-options="{updateOn:'submit'}"
                ng-disabled="Navigation.renamingFolder"
                required
                news-auto-focus>
            <input type="submit"
                value=""
                ng-class="{'icon-loading-small': Navigation.renamingFolder}"
                title="<?php p($l->t('Rename')); ?>"
                class="action icon-checkmark"
                ng-disabled="folderName != folder.name &&
                    !Navigation.renamingFolder &&
                    Navigation.folderNameExists(folderName)">
            </button>
            <p class="error" ng-show="folderName != folder.name &&
                !Navigation.renamingFolder &&
                Navigation.folderNameExists(folderName)">
                <?php p($l->t('Folder exists already!')); ?>
            </p>
            <p class="error" ng-show="folder.renameError">
                {{ folder.renameError }}
            </p>
        </form>
    </div>

    <div class="app-navigation-entry-utils"
         ng-show="folder.id &&
            !folder.editing &&
            !folder.error &&
            !folder.deleted">
        <ul>
            <li class="app-navigation-entry-utils-counter"
                ng-show="folder.id &&
                     Navigation.isFolderUnread(folder.id)"
                title="{{ Navigation.getFolderUnreadCount(folder.id) }}">
                {{ Navigation.getFolderUnreadCount(folder.id) |
                    unreadCountFormatter }}
            </li>
            <li class="app-navigation-entry-utils-menu-button">
                <button title="<?php p($l->t('Menu')); ?>"></button>
            </li>
        </ul>
    </div>

    <div class="app-navigation-entry-menu">
        <ul>
            <li ng-show="Navigation.isFolderUnread(folder.id)" class="mark-read">
                <button ng-click="Navigation.markFolderRead(folder.id)">
                    <span class="icon-checkmark"></span>
                    <span><?php p($l->t('Mark read')); ?></span>
                </button>
            </li>
            <li>
                <button ng-click="folder.editing=true">
                    <span class="icon-rename"></span>
                    <span><?php p($l->t('Rename')); ?></span>
                </button>
            </li>
            <li>
                <button ng-click="Navigation.reversiblyDeleteFolder(folder)">
                    <span class="icon-delete"></span>
                    <span><?php p($l->t('Delete')); ?></span>
                </button>
            </li>
        </ul>
    </div>
    <ul ng-hide="folder.error || folder.deleted">
        <?php print_unescaped(
            $this->inc('part.navigation.feed', ['folderId' => 'folder.id'])
        ); ?>
    </ul>

    <div class="error-message" ng-show="folder.error">
        <h2 class="title">{{ folder.name }}</h2>
        <span class="message">{{ folder.error }}</span>
        <button type="button "
                title="<?php p($l->t('Dismiss')); ?>"
                ng-click="Navigation.deleteFolder(folder)"></button>
    </div>
</li>
