<?php print_unescaped($this->inc('part.content.cronwarning')) ?>

<div id="explore">
    <div ng-repeat="(category, data) in Explore.sites" class="explore-section">
        <h2>{{ category }}</h2>

        <ul>
            <li ng-repeat="entry in data | orderBy:votes:true">
                <h3 ng-style="{ backgroundImage: 'url(' + entry.favicon + ')'}">
                    {{ entry.title }}
                </h3>
                <div>
                    {{ entry.description }}

                    <img ng-src="{{ entry.image }}" ng-if="entry.image">
                </div>
                <div class="explore-subscribe">
                    <button ng-click="Explore.subscribeTo(entry.url)">
                        <?php p($l->t('Subscribe')) ?>
                    </button>
                </div>
            </li>
        </ul>
    </div>
</div>