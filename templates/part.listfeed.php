<li ng-class="{
		active: feedBusinessLayer.isActive(feed.id),
		unread: feedBusinessLayer.getUnreadCount(feed.id) > 0,
		failed: feed.error
	}"
	ng-repeat="feed in feedBusinessLayer.getFeedsOfFolder(<?php p($_['folderId']); ?>) | orderBy:'id':true"
	ng-show="feedBusinessLayer.isVisible(feed.id) || !feed.id"
	data-id="{{ feed.id }}"
	class="feed"
	oc-draggable="{
		stack: '> li',
		zIndex: 1000,
		axis: 'y',
		delay: 200,
		containment: '#app-navigation ul',
		scroll: true,
		revert: true
	}">

	<div ui-if="feed.editing" class="rename-feed" ng-controller="ItemController">
          <input type="text" ng-model="feed.title" autofocus>
          <button title="<?php p($l->t('Cancel')); ?>" 
		ng-click="cancel(feed.id)"
		class="action-button back-button action"></button>
	  <button title="<?php p($l->t('Save')); ?>" 
		ng-click="feedBusinessLayer.renameFeed(feed.id, feed.title)"
		class="action-button create-button action">
	  </button>
    </div>

	<a 	ng-style="{ backgroundImage: feed.faviconLink }"
		ng-click="feedBusinessLayer.load(feed.id)"
		ng-class="{
			'progress-icon': !feed.id,
			'problem-icon': feed.error
		}"
		ng-hide="feed.editing"
	   	href="#"
	   	class="title"
	   	title="{{ feed.title }}"
	   	oc-click-focus="{selector: '#app-content'}">

	   {{ feed.title }}
	</a>

	<span class="utils">
		
		<button ng-click="feedBusinessLayer.delete(feed.id)"
			class="svg action delete-icon delete-button"
			title="<?php p($l->t('Delete website')); ?>"
			ng-show="feed.id && !feed.editing && !feed.error"
			oc-tooltip></button>

		<span class="unread-counter"
			ng-show="feed.id && feedBusinessLayer.getUnreadCount(feed.id) > 0 && !feed.error && !feed.editing">
			{{ unreadCountFormatter(feedBusinessLayer.getUnreadCount(feed.id)) }}
		</span>

		<button class="svg action mark-read-icon"
			ng-show="feedBusinessLayer.getUnreadCount(feed.id) > 0 && feed.id && !feed.error && !feed.editing"
			ng-click="feedBusinessLayer.markRead(feed.id)"
			title="<?php p($l->t('Mark read')); ?>"
			oc-tooltip></button>


		<button class="svg action rename-feed-icon"
			ng-hide="feed.editing || feed.error"
			ng-controller="ItemController"
			ng-click="edit(feed.id)"
			title="<?php p($l->t('Rename feed')); ?>"
			oc-tooltip></button>

		<button class="svg action delete-icon"
			ng-click="feedBusinessLayer.markErrorRead(feed.url)"
			title="<?php p($l->t('Delete website')); ?>"
			ng-show="feed.error"
			oc-tooltip></button>
	</span>

	<div class="message" ng-show="feed.error">{{ feed.error }}</div>
</li>

