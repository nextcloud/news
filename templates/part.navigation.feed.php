<li ng-class="{
        active: Navigation.isFeedActive(feed.id),
        unread: Navigation.isFeedUnread(feed.id)
    }"
    ng-repeat="feed in Navigation.getFeedsOfFolder(<?php p($_['folderId']); ?>)
        | orderBy:'title.toLowerCase()' track by feed.url"
    ng-show="Navigation.isFeedUnread(feed.id)
            || Navigation.isShowAll()
            || Navigation.isFeedActive(feed.id)
            || !feed.id"
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
        ng-show="!feed.editing && !feed.deleted && !feed.error && feed.id"
        ng-href="#/items/feeds/{{ feed.id }}/"
        class="title"
        ng-class="{'icon-rss': !feed.faviconLink}"
        title="{{ feed.title }}">
       {{ feed.title }}
    </a>

    <a ng-hide="feed.id || feed.error"
        class="entry-loading title"
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

    <div class="app-navigation-entry-menu">
        <ul>
            <li>
                <button ng-click="Navigation.setOrdering(feed, 1)"
                        ng-show="feed.ordering == 0"
                        class="icon-caret-dark feed-no-ordering"
                        title="<?php p($l->t('No feed ordering')); ?>">
                </button>
                <button ng-click="Navigation.setOrdering(feed, 2)"
                        ng-show="feed.ordering == 1"
                        class="icon-caret-dark feed-reverse-ordering"
                        title="<?php p($l->t('Reversed feed ordering')); ?>">
                </button>
                <button ng-click="Navigation.setOrdering(feed, 0)"
                        ng-show="feed.ordering == 2"
                        class="icon-caret-dark feed-normal-ordering"
                        title="<?php p($l->t('Normal feed ordering')); ?>">
                </button>
            </li>
            <li>
                <button ng-click="feed.editing=true"
                        class="icon-rename"
                        title="<?php p($l->t('Rename feed')); ?>">
                </button>
            </li>
            <li>
                <button ng-click="Navigation.reversiblyDeleteFeed(feed)"
                        class="icon-delete"
                        title="<?php p($l->t('Delete feed')); ?>">
                </button>
            </li>
            <li ng-show="Navigation.isFeedUnread(feed.id)" class="mark-read">
                <button class="icon-checkmark"
                        ng-click="Navigation.markFeedRead(feed.id)"
                        title="<?php p($l->t('Mark all articles read')); ?>">
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

