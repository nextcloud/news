<li ng-class="{
        active: Navigation.isSubscriptionsActive(),
        unread: Navigation.isUnread()
    }"
    class="subscriptions-feed with-counter with-menu">

    <a class="icon-rss" ng-href="#/items/" ng-if="!Navigation.isShowAll()">
       <?php p($l->t('Unread articles'))?>
    </a>

    <a class="icon-rss" ng-href="#/items/" ng-if="Navigation.isShowAll()">
       <?php p($l->t('All articles'))?>
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
            <li class="mark-read"><button class="icon-checkmark"
                        title="<?php p($l->t('Mark all articles read')); ?>"
                        ng-click="Navigation.markRead()"></button>
            </li>
        </ul>
    </div>

</li>