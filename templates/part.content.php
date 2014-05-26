<div id="first-run" ng-if="App.isFirstRun()">
    <h1><?php p($l->t('Welcome to the ownCloud News app!')) ?></h1>
</div>

<div news-auto-focus="#app-content" ng-if="!App.isFirstRun()">
    <ul ng-if="isCompactView()">

    </ul>
    <ul ng-if="!isCompactView()">
        <li class="article"
            ng-repeat="item in Content.getItems() | orderBy:[Content.orderBy()] track by item.id"
            ng-click="Content.markRead(item.id)"
            ng-class="{read: !item.unread}">

            <h2 class="date">
                <span class="timeago" title="{{item.pubDate*1000|date:'dd-MM-yyyy'}}">
                    {{ getRelativeDate(item.pubDate) }}
                </span>
            </h2>

            <button class="star"
                    ng-click="Content.toggleStar(item.id)"
                    ng-class="{starred: item.starred}"></button>

            <h1 class="title">
                <a target="_blank" ng-href="{{ item.url }}">
                    {{ item.title }}
                </a>
            </h1>

            <div class="item_body" news-bind-html-unsafe="item.body"></div>


        </li>
    </ul>
</div>
