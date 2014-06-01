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
        class="title"
        title="{{ feed.title }}">

       {{ feed.title }}
    </a>

    <span class="utils">

        <!--<button ng-click="Navigation.deleteFeed(feed.id)"
            class="svg action delete-icon delete-button"
            title="<?php p($l->t('Delete website')); ?>"
            ng-show="feed.id && !feed.editing && !feed.error"
            oc-tooltip></button>-->

        <span class="unread-counter"
            ng-show="feed.id && Navigation.getUnreadCount(feed.id) > 0 && !feed.error && !feed.editing">
            {{ Navigation.getFeedUnreadCount(feed.id) | unreadCountFormatter }}
        </span>

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
    -->
    </span>

    <div class="message" ng-show="feed.error">{{ feed.error }}</div>
</li>

