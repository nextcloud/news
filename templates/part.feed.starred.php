<li ng-class="{
		active: Navigation.isStarredActive(),
		unread: Navigation.getStarredCount() > 0
	}"
	class="starred">
	<a class="starred-icon" href="#/items/starred">
	   <?php p($l->t('Starred')) ?>
	</a>
	<span class="utils">
		<span class="unread-counter">
			{{ Navigation.getStarredCount() | unreadCountFormatter }}
		</span>
	</span>
</li>