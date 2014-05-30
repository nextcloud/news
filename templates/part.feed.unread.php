<li ng-class="{
		active: Navigation.isSubscriptionsActive(),
		unread: Navigation.getUnreadCount() > 0
	}">

	<a class="rss-icon" href="#/items" ng-if="!Navigation.isShowAll()">
	   <?php p($l->t('Unread articles'))?>
	</a>

	<a class="rss-icon" href="#/items" ng-if="Navigation.isShowAll()">
	   <?php p($l->t('All articles'))?>
	</a>

	<div class="utils">

	</div>
	<span class="utils">
		<span class="unread-counter" ng-show="Navigation.getUnreadCount() > 0">
			{{ Navigation.getUnreadCount() | unreadCountFormatter }}
		</span>
		<!--
		<button class="svg action mark-read-icon"
			ng-click="Navigation.markRead()"
			title="<?php p($l->t('Mark read')) ?>"
			ng-show="getTotalUnreadCount() > 0"
			oc-tooltip data-placement="bottom"></button>
		-->
	</span>
</li>