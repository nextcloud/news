<li ng-class="{
		active: subscriptionsBusinessLayer.isActive(0),
		unread: getTotalUnreadCount() > 0
	}"
	ng-show="subscriptionsBusinessLayer.isVisible(0)">

	<a class="rss-icon" href="#/items" ng-if="!Navigation.isShowAll()">
	   <?php p($l->t('Unread articles'))?>
	</a>

	<a class="rss-icon" href="#/items" ng-if="Navigation.isShowAll()">
	   <?php p($l->t('All articles'))?>
	</a>

	<div class="utils">

	</div>
	<span class="utils">
		<span class="unread-counter"
			ng-show="getTotalUnreadCount() > 0">
			{{ unreadCountFormatter(getTotalUnreadCount()) }}
		</span>
		<button class="svg action mark-read-icon"
			ng-click="subscriptionsBusinessLayer.markRead()"
			title="<?php p($l->t('Mark read')) ?>"
			ng-show="getTotalUnreadCount() > 0"
			oc-tooltip data-placement="bottom"></button>
	</span>
</li>