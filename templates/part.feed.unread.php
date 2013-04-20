<li ng-class="{
		active: subscriptionsBusinessLayer.isActive(0),
		unread: getTotalUnreadCount() > 0
	}" 
	ng-show="subscriptionsBusinessLayer.isVisible(0)">
	<a class="rss-icon" 
	   href="#" 
	   ui-if="!feedBusinessLayer.isShowAll()"
	   ng-click="subscriptionsBusinessLayer.load(0)">
	   <?php p($l->t('Unread articles'))?>
	</a>
		<a class="rss-icon" 
	   href="#" 
	   ui-if="feedBusinessLayer.isShowAll()"
	   ng-click="subscriptionsBusinessLayer.load(0)"
	   oc-click-focus="{selector: '#app-content'}">
	   <?php p($l->t('All articles'))?>
	</a>
	<span class="utils">
		<span class="unread-counter"
			ng-show="getTotalUnreadCount() > 0">
			{{ getTotalUnreadCount() }}
		</span>
		<button class="svg action mark-read-icon" 
			ng-click="subscriptionsBusinessLayer.markAllRead()"
			title="<?php p($l->t('Mark read')) ?>"
			ng-show="getTotalUnreadCount() > 0"
			oc-tooltip data-placement="bottom"></button>
	</span>
</li>