<li ng-class="{active: isFeedActive(feedType.Feed, feed.id), unread: feed.unreadCount!=0}" 
    ng-repeat="feed in feeds|feedInFolder:<?php p($_['folderId']); ?>"
    ng-show="feed.show"
    data-id="{{feed.id}}"
    class="feed"
    draggable>
	<a ng-style="{backgroundImage: feed.icon}"
	   href="#"
	   class="title"
	   ng-click="loadFeed(feedType.Feed, feed.id)">
	   {{feed.name}}
	</a>
	
	<span class="utils">
		<button ng-click="delete(feedType.Feed, feed.id)"
			class="svg action delete-icon" 
			title="<?php p($l->t('Delete feed')); ?>"></button>

		<button class="svg action mark-read-icon" 
			ng-click="markAllRead(feedType.Feed, feed.id)"
			title="<?php p($l->t('Mark all read')); ?>"></button>

		<span class="unread-counter">
			{{ getUnreadCount(feedType.Feed, feed.id) }}
		</span>


	</span>
</li>
