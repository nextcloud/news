<li ng-class="{
        active: Navigation.isFolderActive(folder.id),
        open: folder.opened || folder.getsFeed,
        collapsible: Navigation.hasFeeds(folder.id) || folder.getsFeed,
        unread: Navigation.getFolderUnreadCount(folder.id) > 0
    }"
    ng-repeat="folder in Navigation.getFolders() | orderBy:'id':true"
    ng-show="Navigation.getFolderUnreadCount(folder.id) > 0
            || Navigation.isShowAll()
            || Navigation.isFolderActive(folder.id)
            || Navigation.subFeedActive(folder.id)
            || !folder.id
            || folder.getsFeed
            || !Navigation.hasFeeds(folder.id)"
    class="folder with-counter with-menu"
    data-id="{{ folder.id }}"
    news-droppable>
    <button class="collapse"
            ng-hide="folder.editing || folder.deleted"
            title="<?php p($l->t('Collapse'));?>"
            ng-click="Navigation.toggleFolder(folder.name)"></button>

    <div ng-if="folder.deleted"
        class="app-navigation-entry-deleted"
        news-timeout="Navigation.deleteFolder(folder)">
        <div class="app-navigation-entry-deleted-description"><?php p($l->t('Deleted folder')); ?>: {{ folder.name }}</div>
        <button class="icon-history app-navigation-entry-deleted-button"
                title="<?php p($l->t('Undo delete folder')); ?>"
                ng-click="Navigation.undoDeleteFolder(folder)"></button>
    </div>

    <div ng-if="folder.editing" class="app-navigation-entry-edit"
        ng-class="{'folder-rename-error': folder.renameError || (folderName != folder.name && !Navigation.renamingFolder && Navigation.folderNameExists(folderName))}">
        <form ng-submit="Navigation.renameFolder(folder, folderName)">
            <fieldset ng-disabled="Navigation.renamingFolder">
                <input name="folderName"
                    type="text"
                    ng-init="folderName=folder.name"
                    ng-class="{'ng-invalid': folderName != folder.name && !Navigation.renamingFolder && Navigation.folderNameExists(folderName)}"
                    ng-model="folderName"
                    required
                    news-auto-focus>
                <input type="submit"
                    value=""
                    ng-class="{'entry-loading': Navigation.renamingFolder}"
                    title="<?php p($l->t('Rename')); ?>"
                    class="action icon-checkmark"
                    ng-disabled="folderName != folder.name && !Navigation.renamingFolder && Navigation.folderNameExists(folderName)">
                </button>
            </fieldset>
            <p class="error" ng-show="folderName != folder.name && !Navigation.renamingFolder && Navigation.folderNameExists(folderName)">
                <?php p($l->t('Folder exists already!')); ?>
            </p>
            <p class="error" ng-show="folder.renameError">{{ folder.renameError }}</p>
        </form>
    </div>

    <a ng-href="#/items/folders/{{ folder.id }}/"
        class="title icon-folder"
        ng-show="!folder.editing && !folder.error && !folder.deleted && folder.id">
       {{ folder.name }}
    </a>

    <a class="title entry-loading" ng-hide="folder.id || folder.error">
       {{ folder.name }}
    </a>

    <div class="app-navigation-entry-utils"
         ng-show="folder.id && !folder.editing && !folder.error && !folder.deleted">
        <ul>
            <li class="app-navigation-entry-utils-counter"
                ng-show="folder.id && Navigation.getFolderUnreadCount(folder.id) > 0"
                title="{{ Navigation.getFolderUnreadCount(folder.id) }}">
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
            <li><button ng-click="Navigation.reversiblyDeleteFolder(folder)"
                        class="icon-delete"
                        title="<?php p($l->t('Delete folder')); ?>"></button></li>
            <li ng-show="Navigation.getFolderUnreadCount(folder.id) > 0"><button class="icon-checkmark"
                        ng-click="Navigation.markFolderRead(folder.id)"
                        title="<?php p($l->t('Read all')); ?>"></button></li>
        </ul>
    </div>
    <ul ng-hide="folder.error || folder.deleted">
        <?php print_unescaped($this->inc('part.navigation.feed', ['folderId' => 'folder.id'])); ?>
    </ul>

    <div class="error-message" ng-show="folder.error">
        <h2 class="title">{{ folder.name }}</h2>
        <span class="message">{{ folder.error }}</span>
        <button type="button "
                title="<?php p($l->t('Dismiss')); ?>"
                ng-click="Navigation.deleteFolder(folder)"></button>
    </div>
</li>
