<?php print_unescaped($this->inc('part.content.cronwarning')) ?>

<div id="explore">
    <div ng-repeat="(category, data) in Explore.sites | orderBy:'category.toLowerCase()'"
         ng-if="Explore.isCategoryShown(data)"
         class="explore-section">
        <h2>{{ category }}</h2>

        <ul>
            <li ng-repeat="entry in data | orderBy:'-votes'" ng-if="!Explore.feedExists(entry.feed)">
                <h3 ng-show="entry.favicon"
                    ng-style="{ backgroundImage: 'url(' + entry.favicon + ')'}">
                    <a target="_blank" ng-href="{{ entry.url }}">{{ entry.title }}</a>
                </h3>
                <h3 ng-hide="entry.favicon" class="icon-rss">
                    {{ entry.title }}
                </h3>
                <div class="explore-content">
                    {{ entry.description }}

                    <div class="explore-logo">
                        <img ng-src="{{ entry.image }}" ng-if="entry.image">
                    </div>
                </div>
                <div class="explore-subscribe">
                    <button ng-click="Explore.subscribeTo(entry.feed)">
                        <?php p($l->t('Subscribe')) ?>
                    </button>
                </div>
            </li>
        </ul>
    </div>
</div>