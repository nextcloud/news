<li ng-class="{
		active: subscriptionsBl.isActive(0),
		unread: subscriptionsBl.getUnreadCount(0) > 0
	}" 
	ng-show="subscriptionsBl.isVisible(0)">
	<a class="rss-icon" 
	   href="#" 
	   ui-if="!feedBl.isShowAll()"
	   ng-click="subscriptionsBl.load(0)">
	   <?php p($l->t('Unread articles'))?>
	</a>
		<a class="rss-icon" 
	   href="#" 
	   ui-if="feedBl.isShowAll()"
	   ng-click="subscriptionsBl.load(0)">
	   <?php p($l->t('All articles'))?>
	</a>
	<span class="utils">
		<span class="unread-counter"
			ng-style="{opacity: getOpacity(subscriptionsBl.getUnreadCount()) }">
			{{ subscriptionsBl.getUnreadCount() }}
		</span>
		<button class="svg action mark-read-icon" 
			ng-click="subscriptionsBl.markAllRead()"
			title="<?php p($l->t('Mark all read')) ?>"></button>
	</span>
</li>