<li ng-class="{
		active: feedBl.isActive(feed.id), 
		unread: feedBl.getUnreadCount(feed.id) > 0,
		failed: feed.error
	}" 
	ng-repeat="feed in feedBl.getFeedsOfFolder(<?php p($_['folderId']); ?>)"
	ng-show="feedBl.isVisible(feed.id)"
	data-id="{{ feed.id }}"
	class="feed"
	oc-draggable="{
		revert: true,
		stack: '> li',
		zIndex: 1000,
		axis: 'y',
		helper: 'clone'
	}">
	<a 	ng-style="{ backgroundImage: feed.faviconLink }"
		ng-click="feedBl.load(feed.id)"
		ng-class="{
			progress-icon: !feed.id,
			problem-icon: feed.error
		}"
	   	href="#"
	   	class="title">
	   	
	   {{ feed.title }}
	</a>
	
	<span class="utils" ng-hide="feed.error">

		<span class="unread-counter">
			{{ feedBl.getUnreadCount(feed.id) }}
		</span>

		<button class="svg action mark-read-icon"
			ng-show="feedBl.getUnreadCount(feed.id) > 0"
			ng-click="feedBl.markFeedRead(feed.id)"
			title="<?php p($l->t('Mark all read')); ?>"></button>
		
		<button ng-click="feedBl.delete(feed.id)"
			class="svg action delete-icon" 
			title="<?php p($l->t('Delete feed')); ?>"></button>

	</span>

	<div class="message" ng-show="feed.error">{{ feed.error }}</div>
</li>

