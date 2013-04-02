<li ng-class="{
		active: isFeedActive(feedType.Feed, feed.id), 
		unread: feed.unreadCount!=0
	}" 
	ng-repeat="feed in feedBl.getFeedsOfFolder(<?php p($_['folderId']); ?>)"
	ng-show="isShown(feedType.Feed, feed.id)"
	data-id="{{feed.id}}"
	class="feed"
	draggable>
	<a ng-style="{backgroundImage: feed.faviconLink}"
	   href="#"
	   class="title"
	   ng-click="loadFeed(feedType.Feed, feed.id)">
	   {{feed.title}}
	</a>
	
	<span class="utils">
		<button ng-click="feedBl.delete(feed.id)"
			class="svg action delete-icon" 
			title="<?php p($l->t('Delete feed')); ?>"></button>

		<span class="unread-counter">
			{{ getFeedUnreadCount(feed.id) }}
		</span>

		<button class="svg action mark-read-icon"
			ng-show="feed.unreadCount > 0"
			ng-click="feedBl.markFeedRead(feed.id)"
			title="<?php p($l->t('Mark all read')); ?>"></button>

	</span>
</li>
