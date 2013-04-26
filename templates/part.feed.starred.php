<li ng-class="{	
		active: starredBusinessLayer.isActive(0), 
		unread: starredBusinessLayer.getUnreadCount() > 0
	}" 
	ng-show="starredBusinessLayer.isVisible(0)"
	class="starred">
	<a class="starred-icon"
		href="#"
		ng-click="starredBusinessLayer.load(0)"
		oc-click-focus="{selector: '#app-content'}">
	   <?php p($l->t('Starred')) ?>
	</a>
	<span class="utils">
		<span class="unread-counter">
			{{ unreadCountFormatter(starredBusinessLayer.getUnreadCount()) }}
		</span>
	</span>
</li>