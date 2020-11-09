<li ng-class="{
        active: Navigation.isFeedActive(feed.id),
        unread: Navigation.isFeedUnread(feed.id),
        updateerror: feed.updateErrorCount>50,
        deleted: feed.deleted,
        editing: feed.editing,
        'icon-loading-small': !(feed.id || feed.error)
    }"
    ng-repeat="feed in Navigation.getFeedsOfFolder(<?php p($_['folderId']); ?>)
        | orderBy:['-pinned', 'title.toLowerCase()']:false:localeComparator track by feed.url"
    ng-show="Navigation.isFeedUnread(feed.id)
            || Navigation.isShowAll()
            || Navigation.isFeedActive(feed.id)
            || !feed.id
            || feed.updateErrorCount>50"
    data-id="{{ feed.id }}"
    class="feed with-counter with-menu animate-show"
    news-draggable-disable="{{
        feed.error.length > 0 ||
        !feed.id ||
        feed.deleted ||
        feed.editing
    }}"
    news-draggable="{
        stack: '> li',
        zIndex: 1000,
        axis: 'y',
        delay: 200,
        containment: '#app-navigation ul',
        scroll: true,
        revert: true
    }">

    <a  ng-style="{ backgroundImage: 'url(' + feed.faviconLink + ')'}"
        ng-show="!feed.error && feed.id"
        ng-href="#/items/feeds/{{ feed.id }}/"
        class="title"
        ng-class="{'icon-rss': !feed.faviconLink}"
        title="{{ feed.updateErrorCount>50 ? '<?php p(addslashes($l->t('Update failed more than 50 times'))); ?>: ' + feed.lastUpdateError : feed.title }}">
       {{ feed.title }}
    </a>

    <a ng-if="!(feed.id || feed.error)"
        class="title"
        title="{{ feed.title }}">
       {{ feed.title }}
    </a>

    <div ng-if="feed.deleted"
         class="app-navigation-entry-deleted"
         news-timeout="Navigation.deleteFeed(feed)">
        <div class="app-navigation-entry-deleted-description">
            <?php p($l->t('Deleted feed')); ?>: {{ feed.title }}
        </div>
        <button class="icon-history app-navigation-entry-deleted-button"
                title="<?php p($l->t('Undo delete feed')); ?>"
                ng-click="Navigation.undoDeleteFeed(feed)"></button>
    </div>

    <div ng-if="feed.editing" class="app-navigation-entry-edit">
        <form ng-submit="Navigation.renameFeed(feed)">
            <input name="feedRename"
                type="text"
                ng-model="feed.title"
                news-auto-focus
                ng-model-options="{updateOn:'submit'}"
                required>
            <input type="submit"
                value=""
                title="<?php p($l->t('Rename')); ?>"
                    class="action icon-checkmark">
        </form>
    </div>

    <div class="app-navigation-entry-utils"
         ng-show="feed.id && !feed.editing && !feed.error && !feed.deleted">
        <ul>
            <li class="app-navigation-entry-utils-counter"
                ng-show="feed.id && Navigation.isFeedUnread(feed.id)"
                title="{{ Navigation.getFeedUnreadCount(feed.id) }}">
                {{ Navigation.getFeedUnreadCount(feed.id) |
                    unreadCountFormatter }}
            </li>
            <li class="app-navigation-entry-utils-menu-button">
                <button title="<?php p($l->t('Menu')); ?>"></button>
            </li>
        </ul>
    </div>

    <div class="app-navigation-entry-menu" ng-click="$event.stopPropagation()">
        <ul>
            <li ng-show="Navigation.isFeedUnread(feed.id)" class="mark-read">
                <button ng-click="Navigation.markFeedRead(feed.id)">
                    <span class="icon-checkmark"></span>
                    <span><?php p($l->t('Mark read')); ?></span>
                </button>
            </li>
            <li>
                <button ng-click="Navigation.togglePinned(feed.id)"
                        ng-show="feed.pinned">
                    <span class="icon-pinned"></span>
                    <span><?php p($l->t('Unpin from top')); ?></span>
                </button>
                <button ng-click="Navigation.togglePinned(feed.id)"
                        ng-hide="feed.pinned">
                    <span class="icon-unpinned"></span>
                    <span><?php p($l->t('Pin to top')); ?></span>
                </button>
            </li>
            <li>
                <button ng-click="Navigation.setOrdering(feed, 1)"
                        ng-show="feed.ordering == 0">
                    <span class="icon-caret-dark feed-no-ordering"></span>
                    <span><?php p($l->t('Newest first')); ?></span>
                </button>
                <button ng-click="Navigation.setOrdering(feed, 2)"
                        ng-show="feed.ordering == 1">
                    <span class="icon-caret-dark feed-reverse-ordering"></span>
                    <span><?php p($l->t('Oldest first')); ?></span>
                </button>
                <button ng-click="Navigation.setOrdering(feed, 0)"
                        ng-show="feed.ordering == 2">
                    <span class="icon-caret-dark feed-normal-ordering"></span>
                    <span><?php p($l->t('Default order')); ?></span>
                </button>
            </li>
            <li>
                <button ng-click="Navigation.toggleFullText(feed)"
                        ng-hide="feed.fullTextEnabled">
                    <span class="icon-full-text-disabled"></span>
                    <span><?php p($l->t('Enable full text')); ?></span>
                </button>
                <button ng-click="Navigation.toggleFullText(feed)"
                        ng-show="feed.fullTextEnabled">
                    <span class="icon-full-text-enabled"></span>
                    <span><?php p($l->t('Disable full text')); ?></span>
                </button>
            </li>
            <li>
                <button ng-click="Navigation.setUpdateMode(feed.id, 1)"
                        ng-hide="feed.updateMode == 1">
                    <span class="icon-updatemode-default"></span>
                    <span><?php p($l->t('Unread updated')); ?></span>
                </button>
                <button ng-click="Navigation.setUpdateMode(feed.id, 0)"
                        ng-show="feed.updateMode == 1">
                    <span class="icon-updatemode-unread"></span>
                    <span><?php p($l->t('Ignore updated')); ?></span>
                </button>
            </li>
            <li>
                <a ng-href="{{feed.url}}" target="_blank" rel="noopener noreferrer">
                    <span class="icon-rss"></span>
                    <span><?php p($l->t('Open feed URL')); ?></span>
                </a>
            </li>
            <li>
                <button ng-click="feed.editing=true">
                    <span class="icon-rename"></span>
                    <span><?php p($l->t('Rename')); ?></span>
                </button>
            </li>
            <li>
                <button ng-click="Navigation.reversiblyDeleteFeed(feed)">
                    <span class="icon-delete"></span>
                    <span><?php p($l->t('Delete')); ?></span>
                </button>
            </li>
        </ul>
    </div>

    <div class="error-message" ng-show="feed.error">
        <h2 class="title">{{ feed.url }}</h2>
        <span class="message">{{ feed.error }}</span>
        <button type="button "
                title="<?php p($l->t('Dismiss')); ?>"
                ng-click="Navigation.deleteFeed(feed)"></button>
    </div>
</li>
