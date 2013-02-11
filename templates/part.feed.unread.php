<li ng-class="{
		active: isFeedActive(feedType.Subscriptions, 0),
		all_read: getUnreadCount(feedType.Subscriptions, 0)==0
	}" 
	class="subscriptions"
	ng-show="isShown(feedType.Subscriptions, 0)">
	<a class="title" 
	   href="#" 
	   ng-click="loadFeed(feedType.Subscriptions, 0)">
	   <?php p($l->t('New articles'))?>
	</a>
	<span class="utils">
		<span class="unread-counter">
			{{ getUnreadCount(feedType.Starred, 0) }}
		</span>
		<button class="svg action mark-read-icon" 
			ng-click="markAllRead(feedType.Subscriptions, 0)"
			title="<?php p($l->t('Mark all read')) ?>"></button>
	</span>
</li>