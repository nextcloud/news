<li ng-class="{
        active: Navigation.isUnreadActive(),
        unread: Navigation.isUnread()
    }"
    class="subscriptions-feed with-counter with-menu">

    <a class="icon-rss-unread" ng-href="#/items/unread/" >
       <?php p($l->t('Unread articles'))?>
    </a>

    <div class="app-navigation-entry-utils" ng-show="Navigation.isUnread()">
        <ul>
            <li class="app-navigation-entry-utils-counter"
                ng-show="Navigation.isUnread()"
                title="{{ Navigation.getUnreadCount() }}">
                {{ Navigation.getUnreadCount() | unreadCountFormatter }}
            </li>
            <li class="app-navigation-entry-utils-menu-button">
                <button
                    ng-click="optionsId = (optionsId == 'all' ? -1 : 'all')">
                </button>
            </li>
        </ul>
    </div>

    <div class="app-navigation-entry-menu">
        <ul>
            <li class="mark-read">
                <button ng-click="Navigation.markRead()">
                    <span class="icon-checkmark"></span>
                    <span><?php p($l->t('Mark read')); ?></span>
                </button>
            </li>
        </ul>
    </div>

</li>

<li ng-class="{
        active: Navigation.isSubscriptionsActive(),
        unread: Navigation.isUnread()
    }"
    ng-if="Navigation.isShowAll()"
    class="all-subscriptions-feed with-menu">

    <a class="icon-rss" ng-href="#/items/">
       <?php p($l->t('All articles'))?>
    </a>

    <div class="app-navigation-entry-utils">
        <ul>
            <li class="app-navigation-entry-utils-menu-button">
                <button
                    ng-click="optionsId = (optionsId == 'all' ? -1 : 'all')">
                </button>
            </li>
        </ul>
    </div>

    <div class="app-navigation-entry-menu">
        <ul>
            <li class="mark-read">
                <button ng-click="Navigation.markRead()">
                    <span class="icon-checkmark"></span>
                    <span><?php p($l->t('Mark read')); ?></span>
                </button>
            </li>
        </ul>
    </div>

</li>
