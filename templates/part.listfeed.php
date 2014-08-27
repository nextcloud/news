<li ng-class="{
        active: Navigation.isFeedActive(feed.id),
        unread: Navigation.getFeedUnreadCount(feed.id) > 0,
        failed: feed.error
    }"
    ng-repeat="feed in Navigation.getFeedsOfFolder(<?php p($_['folderId']); ?>) | orderBy:'id':true track by feed.url"
    ng-show="Navigation.getFeedUnreadCount(feed.id) > 0
            || Navigation.isShowAll()
            || Navigation.isFeedActive(feed.id)
            || !feed.id"
    data-id="{{ feed.id }}"
    class="feed"
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
        ng-class="{
            'progress-icon': !feed.id,
            'problem-icon': feed.error
        }"
        ng-if="!feed.editing"
        ng-href="#/items/feeds/{{ feed.id }}/"
        class="title icon-loading"
        title="{{ feed.title }}">
       {{ feed.title }}
    </a>

    <div ng-if="feed.editing" class="app-navigation-entry-edit">
        <input name="feedRename" type="text" value="{{ feed.title }}" news-auto-focus>
        <button title="<?php p($l->t('Cancel')); ?>"
                ng-click="feed.editing=false"
                class="action icon-close"></button>
        <button title="<?php p($l->t('Rename')); ?>"
                ng-click="Navigation.renameFeed(feed.id, feed.title)"
                class="action icon-checkmark">
        </button>
    </div>

    <div class="app-navigation-entry-utils"
         ng-show="feed.id && !feed.editing && !feed.error">
        <ul>
            <li class="app-navigation-entry-utils-counter"
                ng-show="feed.id && Navigation.getUnreadCount(feed.id) > 0">
                {{ Navigation.getFeedUnreadCount(feed.id) | unreadCountFormatter }}
            </li>
            <li class="app-navigation-entry-utils-menu-button">
                <button ng-click="App.toggleMenu('f' + feed.id)"></button>
            </li>
        </ul>
    </div>

    <div class="app-navigation-entry-menu">
        <ul>
            <li><button ng-click="feed.editing=true"
                        class="icon-rename"
                        title="<?php p($l->t('Rename feed')); ?>"></button></li>
            <li><button ng-click="Navigation.deleteFeed(feed.url)"
                        class="icon-delete"
                        title="<?php p($l->t('Delete website')); ?>"></button></li>
            <li><button ng-show="Navigation.getUnreadCount(feed.id) > 0"
                        class="icon-checkmark"
                        title="<?php p($l->t('Read all')); ?>"></button></li>
        </ul>
    </div>

    <div class="message" ng-show="feed.error">{{ feed.error }}</div>
</li>

