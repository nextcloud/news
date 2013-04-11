<li ng-class="{	
		active: starredBl.isActive(0), 
		unread: starredBl.getUnreadCount() > 0
	}" 
	ng-show="starredBl.isVisible(0)"
	class="starred">
	<a class="starred-icon"
		href="#"
		ng-click="starredBl.load(0)">
	   <?php p($l->t('Starred')) ?>
	</a>
	<span class="utils">
		<span class="unread-counter">
			{{ starredBl.getUnreadCount() }}
		</span>
	</span>
</li>