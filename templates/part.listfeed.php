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

    <div ng-if="feed.editing" class="rename-feed">
          <input type="text" ng-model="feed.title" autofocus>
          <button title="<?php p($l->t('Cancel')); ?>"
        ng-click="cancelRenameFeed(feed.id)"
        class="action-button back-button action"></button>
      <button title="<?php p($l->t('Save')); ?>"
        ng-click="Navigation.renameFeed(feed.id, feed.title)"
        class="action-button create-button action">
      </button>
    </div>

    <a  ng-style="{ backgroundImage: 'url(' + feed.faviconLink + ')'}"
        ng-class="{
            'progress-icon': !feed.id,
            'problem-icon': feed.error
        }"
        ng-hide="feed.editing"
        ng-href="#/items/feeds/{{ feed.id }}/"
        class="title  icon-loading"
        title="{{ feed.title }}">

       {{ feed.title }}
    </a>

    <div class="app-navigation-entry-utils">
        <ul>
            <li class="app-navigation-entry-utils-counter"
                ng-show="feed.id && Navigation.getUnreadCount(feed.id) > 0 && !feed.error && !feed.editing">
                {{ Navigation.getFeedUnreadCount(feed.id) | unreadCountFormatter }}
            </li>
            <li><button class="app-navigation-entry-utils-menu-button" ng-click="optionsId = (optionsId == feed.id ? -1 : feed.id)"></button></li>
        </ul>
    </div>

    <div class="app-navigation-entry-menu" ng-class="{'app-navigation-entry-menu-open': optionsId == feed.id}">
        <ul>
            <li><button class="icon-rename" title="<?php p($l->t('Rename feed')); ?>"></button></li>
            <li><button class="icon-delete" title="<?php p($l->t('Delete website')); ?>"></button></li>
        </ul>
    </div>

    <!--<span class="utils">
        <span class="unread-counter"
            >

        </span>-->
        <!--<button ng-click="Navigation.deleteFeed(feed.id)"
            class="svg action delete-icon delete-button"
            title="<?php p($l->t('Delete website')); ?>"
            ng-show="feed.id && !feed.editing && !feed.error"
            oc-tooltip></button>-->


    <!--
        <button class="svg action mark-read-icon"
            ng-show="Navigation.getUnreadCount(feed.id) > 0 && feed.id && !feed.error && !feed.editing"
            ng-click="Navigation.markRead(feed.id)"
            title="<?php p($l->t('Mark read')); ?>"
            oc-tooltip></button>


        <button class="svg action rename-feed-icon"
            ng-hide="feed.editing || feed.error"
            ng-click="edit(feed)"
            title="<?php p($l->t('Rename feed')); ?>"
            oc-tooltip></button>

        <button class="svg action delete-icon"
            ng-click="Navigation.markErrorRead(feed.url)"
            title="<?php p($l->t('Delete website')); ?>"
            ng-show="feed.error"
            oc-tooltip></button>
    </span>
    -->

    <div class="message" ng-show="feed.error">{{ feed.error }}</div>
</li>

