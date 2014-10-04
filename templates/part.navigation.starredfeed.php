<li ng-class="{
		active: Navigation.isStarredActive(),
		unread: Navigation.getStarredCount() > 0
	}"
	class="with-counter starred-feed">

	<a class="icon-starred" ng-href="#/items/starred/">
	   <?php p($l->t('Starred')) ?>
	</a>

	<div class="app-navigation-entry-utils">
        <ul>
            <li class="app-navigation-entry-utils-counter"
                ng-show="Navigation.getStarredCount() > 0"
                title="{{ Navigation.getStarredCount() }}">
                {{ Navigation.getStarredCount() | unreadCountFormatter }}
            </li>
        </ul>
    </div>
</li>