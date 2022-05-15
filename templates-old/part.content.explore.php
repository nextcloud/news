<?php print_unescaped($this->inc('part.content.warnings')) ?>

<div id="explore">
    <div class="grid">
        <div ng-repeat="entry in Explore.feeds | filter:Explore.filter | orderBy:'-votes'" ng-show="!Explore.feedExists(entry.feed)" class="explore-feed grid-item" news-refresh-masonry>
            <h2 ng-show="entry.favicon"
				class="explore-title"
                ng-style="{ backgroundImage: 'url(' + entry.favicon + ')'}">
                <a target="_blank" rel="noreferrer" ng-href="{{ entry.url }}">{{ entry.title }}</a>
            </h2>
            <h2 ng-hide="entry.favicon" class="icon-rss explore-title">
                {{ entry.title }}
            </h2>
            <div class="explore-content">
                <p>{{ entry.description }}</p>

                <div ng-if="entry.image" class="explore-logo">
                    <img ng-src="{{ entry.image }}" >
                </div>
            </div>
            <button class="explore-subscribe" ng-click="Explore.subscribeTo(entry.feed)">
                <?php p($l->t('Subscribe to')) ?> {{ entry.title }}
            </button>
        </div>
    </div>
</div>
