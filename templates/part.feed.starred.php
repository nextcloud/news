<li ng-class="{	active: isFeedActive(feedType.Starred, 0) }" 
	ng-show="isShown(feedType.Starred, 0)">
	<a class="starred-icon"
		href="#"
		ng-click="loadFeed(feedType.Starred, 0)">
	   <?php p($l->t('Starred')) ?>
	</a>
	<span class="utils">
		<span class="unread-counter">
			{{ getStarredCount() }}
		</span>
	</span>
</li>