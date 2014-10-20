<?php print_unescaped($this->inc('part.content.firstrun')) ?>

<div news-auto-focus="#app-content"
    id="articles"
     ng-class="{compact: Content.isCompactView(), 'feed-view': Content.isFeed()}">
    <div class="pull-to-refresh" ng-class="{'show-pull-to-refresh': showPullToRefresh}">
        <button ng-click="Content.refresh()"><?php p($l->t('Refresh')) ?></button>
    </div>
    <ul>
        <li class="item {{ Content.getFeed(item.feedId).cssClass }}"
            ng-repeat="item in Content.getItems() | orderBy:[Content.orderBy()] track by item.id"
            ng-click="Content.markRead(item.id)"
            ng-class="{read: !item.unread, open: item.show}"
            data-id="{{ item.id }}">

            <div class="utils" ng-click="Content.toggleItem(item)">
                <ul>
                    <li class="util-spacer"></li>
                    <li class="util only-in-compact">
                        <a class="external icon-link"
                            target="_blank"
                            ng-href="{{ item.url }}"
                            title="<?php p($l->t('Open website')) ?>"
                            news-stop-propagation>
                        </a>
                    </li>
                    <li class="title only-in-compact"
                        title="{{ item.title }}"
                        ng-class="{'icon-rss': !Content.getFeed(item.feedId).faviconLink }"
                        ng-style="{ backgroundImage: 'url(' + Content.getFeed(item.feedId).faviconLink + ')'}">
                        <h1><a>{{ item.title }}</a></h1>
                    </li>
                    <li class="only-in-compact">
                        <time class="date" title="{{ item.pubDate*1000|date:'yyyy-MM-dd HH:mm:ss' }}"
                            datetime="{{ item.pubDate*1000|date:'yyyy-MM-ddTHH:mm:ssZ' }}">{{ Content.getRelativeDate(item.pubDate) }}
                        </time>
                    </li>
                    <li ng-click="Content.toggleStar(item.id)" class="util" news-stop-propagation>
                        <button class="star svg" ng-hide="item.starred" title="<?php p($l->t('Star article')); ?>"></button>
                        <button class="starred svg" ng-show="item.starred" title="<?php p($l->t('Unstar article')); ?>"></button>
                    </li>
                    <li ng-click="Content.toggleKeepUnread(item.id)" class="util" news-stop-propagation>
                        <button class="icon-toggle toggle-keep-unread" ng-hide="item.keepUnread" title="<?php p($l->t('Keep article unread')); ?>"></button>
                        <button class="icon-toggle toggle-keep-unread keep-unread" ng-show="item.keepUnread" title="<?php p($l->t('Remove keep article unread')); ?>"></button>
                    </li>
                </ul>
            </div>

            <div class="article">

                <div class="heading only-in-expanded">
                    <time class="date" title="{{ item.pubDate*1000|date:'yyyy-MM-dd HH:mm:ss' }}"
                        datetime="{{ item.pubDate*1000|date:'yyyy-MM-ddTHH:mm:ssZ' }}">{{ Content.getRelativeDate(item.pubDate) }}</time>
                    <h1>
                        <a class="external"
                            target="_blank"
                            ng-href="{{ item.url }}"
                            title="{{ item.title }}">
                            {{ item.title }}
                        </a>
                    </h1>
                </div>

                <div class="subtitle">
                    <span class="author" ng-show="item.author"> <?php p($l->t('by')) ?> {{ item.author }}</span>
                    <span class="source"><?php p($l->t('from')) ?> <a ng-href="#/items/feeds/{{ item.feedId }}/">{{ Content.getFeed(item.feedId).title }}</a></span>
                </div>



                <div class="enclosure" ng-if="item.enclosureLink">
                    <news-enclosure type="{{ item.enclosureMime }}"
                                link="{{ item.enclosureLink }}">
                        <p class="enclosure-error">
                            <?php p($l->t('Browser can not play media type')) ?>:
                            {{ item.enclosureMime }}
                        </p>
                        <a class="button"
                           ng-href="{{ item.enclosureLink | trustUrl }}"
                           target="_blank">
                            <?php p($l->t('Download')) ?>
                        </a>
                    </news-enclosure>
                </div>

                <div class="body" news-bind-html-unsafe="item.body"></div>

            </div>
        </li>
    </ul>
</div>
