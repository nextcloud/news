<?php
use OCA\News\Plugin\Client\Plugin;

script('news', [
    'vendor/js-url/url.min',
    'vendor/es6-shim/es6-shim.min',
    'vendor/angular/angular.min',
    'vendor/angular-animate/angular-animate.min',
    'vendor/angular-route/angular-route.min',
    'vendor/angular-sanitize/angular-sanitize.min',
    'vendor/momentjs/min/moment-with-locales.min',
    'build/app.min'
]);

style('news', 'news.min');

// load plugin scripts and styles
foreach (Plugin::getStyles() as $appName => $fileName) {
    style($appName, $fileName);
}
foreach (Plugin::getScripts() as $appName => $fileName) {
    script($appName, $fileName);
}
?>

<div id="app" ng-app="News" ng-cloak ng-strict-di
    ng-controller="AppController as App">


    <div id="global-loading"
        class="icon-loading"
        ng-show="App.loading.isLoading('global')"></div>

    <!-- navigation -->
    <div id="app-navigation"
        ng-controller="NavigationController as Navigation"
        ng-hide="App.loading.isLoading('global')">

        <news-search on-search="Navigation.search" class="ng-hide"></news-search>
        <news-title-unread-count
            unread-count="{{ Navigation.getUnreadCount() }}">
        </news-title-unread-count>

        <ul class="with-icon" data-id="0" news-droppable>
            <?php print_unescaped($this->inc('part.navigation.addfeed')) ?>
            <?php print_unescaped($this->inc('part.navigation.addfolder')) ?>
            <?php print_unescaped($this->inc('part.navigation.unreadfeed')) ?>
            <?php print_unescaped($this->inc('part.navigation.starredfeed')) ?>
            <?php print_unescaped($this->inc(
                'part.navigation.feed', ['folderId' => '0']
            )) ?>
            <?php print_unescaped($this->inc('part.navigation.folder')) ?>
            <?php print_unescaped($this->inc('part.navigation.explore')) ?>
        </ul>

        <!-- settings -->
        <div id="app-settings" ng-controller="SettingsController as Settings">
            <?php print_unescaped($this->inc('part.settings')) ?>
        </div>
    </div>

    <!-- content -->
    <script type="text/ng-template" id="audio.html">
        <?php print_unescaped($this->inc('part.content.audio')) ?>
    </script>
    <script type="text/ng-template" id="articleaction.html">
        <?php print_unescaped($this->inc('part.content.articleaction')) ?>
    </script>
    <script type="text/ng-template" id="content.html">
        <?php print_unescaped($this->inc('part.content')) ?>
    </script>
    <script type="text/ng-template" id="shortcuts.html">
        <?php print_unescaped($this->inc('part.content.shortcuts')) ?>
    </script>
    <script type="text/ng-template" id="explore.html">
        <?php print_unescaped($this->inc('part.content.explore')) ?>
    </script>

    <div id="app-content"
        ng-class="{
            'loading-content': App.loading.isLoading('content') &&
                               !App.loading.isLoading('global'),
            'explore': App.isFirstRun()
        }"
        tabindex="-1"
        news-pull-to-refresh="showPullToRefresh">
        <div class="podcast" news-sticky-menu="#app-content" ng-if="App.playingItem">
            <audio controls autoplay ng-src="{{ App.playingItem.enclosureLink|trustUrl }}" news-play-one></audio>
            <a class="button podcast-download" title="<?php p($l->t('Download')) ?>"
                ng-href="{{ App.playingItem.enclosureLink|trustUrl }}"
                target="_blank"></a>
            <button class="podcast-close" title="<?php p($l->t('Close')) ?>"
                ng-click="App.playingItem = false"></button>
        </div>
        <div id="app-content-wrapper"
            ng-class="{
                'autopaging': App.loading.isLoading('autopaging'),
                'finished-auto-paging': Content.isNothingMoreToAutoPage
            }"
            ng-hide="App.loading.isLoading('global')"
            ng-view
            news-scroll="#app-content"
            news-scroll-enabled-mark-read="Content.markReadEnabled()"
            news-scroll-auto-page="Content.autoPage()"
            news-scroll-mark-read="Content.scrollRead(itemIds)"></div>
</div>
