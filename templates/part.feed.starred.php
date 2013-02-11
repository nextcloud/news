<li ng-class="{
                active: isFeedActive(feedType.Starred, 0),
                all_read: getUnreadCount(feedType.Starred, 0)==0
        }"
        class="starred-icon"
        ng-show="isShown(feedType.Starred, 0)">
        <a class="title"
           href="#"
           ng-click="loadFeed(feedType.Starred, 0)">
           <?php p($l->t('Starred')) ?>
        </a>
        <span class="utils">
                <span class="unread_items_counter">
                        {{ getUnreadCount(feedType.Starred, 0) }}
                </span>
        </span>
</li>