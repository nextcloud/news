<div id="first-run" ng-if="App.isFirstRun()">
    <h1><?php p($l->t('Welcome to the ownCloud News app!')) ?></h1>
</div>

<div ng-if="!App.isFirstRun()">
    <ul>
        <li class="item {{ Content.getFeed(item.feedId).cssClass }}"
		    ng-repeat="item in Content.getItems() | orderBy:[Content.orderBy()] track by item.id"
		    ng-click="Content.markRead(item.id)"
		    ng-class="{read: !item.unread}"
		    data-id="{{ item.id }}">

		    <div class="utils" ng-click="Content.toggleItem(item)">
		        <ul>
		            <li ng-click="Content.toggleStar(item.id)" class="util" news-stop-propagation>
		                <button class="star svg" ng-class="{'starred': item.starred}" title="<?php p($l->t('Star')); ?>"></button>
		            </li>
		            <li class="util">
		                <a class="external icon-link"
		                    target="_blank"
		                    ng-href="{{ item.url }}"
		                    title="<?php p($l->t('Open website')) ?>"
		                    news-stop-propagation>
		                </a>
		            </li>
		            <li ng-click="Content.toggleKeepUnread(item.id)" class="util" news-stop-propagation>
		                <button class="icon-toggle" ng-class="{'keep-unread': item.keepUnread}" title="<?php p($l->t('Keep unread')); ?>"></button>
		            </li>
		            <li class="title">
		                <h1>
		                    <a target="_blank" >
		                        {{ item.title }}
		                    </a>
		                </h1>
		            </li>
		            <li class="source">
		                <a ng-href="#/items/feeds/{{ item.feedId }}/" news-stop-propagation>{{ Content.getFeed(item.feedId).title }}</a>
		            </li>
		            <li class="date">
		                <time title="{{ item.pubDate*1000|date:'yyyy-MM-dd HH:mm:ss' }}"
		                    datetime="{{ item.pubDate*1000|date:'yyyy-MM-ddTHH:mm:ssZ' }}">
		                    {{ Content.getRelativeDate(item.pubDate) }}
		                </time>
		            </li>
		        </ul>
		    </div>

		    <div class="article" ng-show="!Content.isCompactView() || item.show">

		        <div class="enclosure" ng-if="item.enclosureLink">
		            <news-audio type="{{ item.enclosureType }}"
		                        ng-src="{{ item.enclosureLink|trustUrl }}">
		                <?php p($l->t('Download')) ?>
		            </news-audio>
		        </div>

		        <div class="body" news-bind-html-unsafe="item.body"></div>

		        <h2 class="author" ng-show="item.author">© {{ item.author }}</h2>
		    </div>
		</li>
    </ul>
</div>
