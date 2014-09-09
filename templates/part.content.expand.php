<li class="item {{ Content.getFeed(item.feedId).cssClass }}"
    ng-repeat="item in Content.getItems() | orderBy:[Content.orderBy()] track by item.id"
    ng-click="Content.markRead(item.id)"
    ng-class="{read: !item.unread}"
    data-id="{{ item.id }}">

    <div class="utils">
        <ul>
            <li ng-click="Content.toggleStar(item.id)">
                <button class="star svg" ng-class="{'starred': item.starred}" title="<?php p($l->t('Star')); ?>"></button>
            </li>
            <li ng-click="Content.toggleKeepUnread(item.id)">
                <button class="star svg" ng-class="{'starred': item.keepUnread}" title="<?php p($l->t('Keep unread')); ?>"></button>
            </li>
        </ul>
    </div>

    <div class="article">
        <time class="date" title="{{ item.pubDate*1000|date:'yyyy-MM-dd HH:mm:ss' }}"
            datetime="{{ item.pubDate*1000|date:'yyyy-MM-ddTHH:mm:ssZ' }}"">
            {{ Content.getRelativeDate(item.pubDate) }}
        </time>

        <h1 class="title">
            <a target="_blank" ng-href="{{ item.url }}">
                {{ item.title }}
            </a>
        </h1>

        <h2 class="author">
            <span>
                <?php p($l->t('from')) ?>
                <a ng-href="#/items/feeds/{{ item.feedId }}"
                    class="from_feed">{{ Content.getFeed(item.feedId).title }}</a>
            </span>
            <span ng-show="item.author">
                <?php p($l->t('by')) ?>
                {{ item.author }}
            </span>
        </h2>

        <div class="enclosure" ng-if="item.enclosureLink">
            <news-audio type="{{ item.enclosureType }}"
                        ng-src="{{ item.enclosureLink|trustUrl }}">
                <?php p($l->t('Download')) ?>
            </news-audio>
        </div>

        <div class="body" news-bind-html-unsafe="item.body"></div>

    </div>
</li>

