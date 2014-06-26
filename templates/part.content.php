<div id="first-run" ng-if="App.isFirstRun()">
    <h1><?php p($l->t('Welcome to the ownCloud News app!')) ?></h1>
</div>

<div news-auto-focus="#app-content" ng-if="!App.isFirstRun()">
    <!-- compact view -->
    <ul ng-if="Content.isCompactView()" class="compact">

    </ul>

    <!-- full view -->
    <ul ng-if="!Content.isCompactView()">
        <li class="item"
            ng-repeat="item in Content.getItems() | orderBy:[Content.orderBy()] track by item.id"
            ng-click="Content.markRead(item.id)"
            ng-class="{read: !item.unread}"
            data-id="{{ item.id }}">

            <h2 class="date">
                <span class="timeago" title="{{ item.pubDate*1000|date:'dd-MM-yyyy' }}">
                    {{ Content.getRelativeDate(item.pubDate) }}
                </span>
            </h2>

            <button class="star svg"
                    ng-click="Content.toggleStar(item.id)"
                    ng-class="{
                        'starred': item.starred
                    }"></button>

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

            <div class="bottom-utils">
                <ul>
                    <li ng-click="Content.toggleKeepUnread(item.id)">
                        <label for="keep-unread">
                            <input type="checkbox" name="keep-unread" ng-checked="item.keepUnread"/>
                            <?php p($l->t('Keep unread')); ?>
                        </label>
                    </li>
                </ul>
            </div>
        </li>
    </ul>
</div>
