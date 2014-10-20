<?php
// backports
if (version_compare(implode('.', \OCP\Util::getVersion()), '7.8', '<=')) {
    style('news', 'news-owncloud7.min');
} else {
    style('news', 'news.min');
}

script('news', [
    'vendor/es6-shim/es6-shim.min',
    'vendor/angular/angular.min',
    'vendor/angular-route/angular-route.min',
    'vendor/angular-sanitize/angular-sanitize.min',
    'vendor/momentjs/min/moment-with-locales.min',
    'build/app.min',
]);
?>

<div id="app" ng-app="News" ng-cloak ng-strict-di ng-controller="AppController as App">

    <div id="global-loading" class="icon-loading" ng-show="App.loading.isLoading('global')"></div>

    <!-- navigation -->
    <div id="app-navigation" ng-controller="NavigationController as Navigation" ng-hide="App.loading.isLoading('global')">
        <news-title-unread-count unread-count="{{ Navigation.getUnreadCount() }}"></news-title-unread-count>

        <ul class="with-icon" data-id="0" news-droppable>
            <?php print_unescaped($this->inc('part.navigation.addfeed')) ?>
            <?php print_unescaped($this->inc('part.navigation.addfolder')) ?>
            <?php print_unescaped($this->inc('part.navigation.unreadfeed')) ?>
            <?php print_unescaped($this->inc('part.navigation.starredfeed')) ?>
            <?php print_unescaped($this->inc('part.navigation.feed', ['folderId' => '0'])) ?>
            <?php print_unescaped($this->inc('part.navigation.folder')) ?>
        </ul>

        <!-- settings -->
        <div id="app-settings" ng-controller="SettingsController as Settings">
            <?php print_unescaped($this->inc('part.settings')) ?>
        </div>
    </div>

    <!-- content -->
    <script type="text/ng-template" id="content.html"><?php print_unescaped($this->inc('part.content')) ?></script>
    <script type="text/ng-template" id="shortcuts.html"><?php print_unescaped($this->inc('part.content.shortcuts')) ?></script>

    <div id="app-content"
        ng-class="{
            'loading-content': App.loading.isLoading('content') && !App.loading.isLoading('global'),
            'first-run': App.isFirstRun()
        }"
        tabindex="-1"
        news-pull-to-refresh="showPullToRefresh">
        <div id="app-content-wrapper"
            ng-class="{'autopaging': App.loading.isLoading('autopaging')}"
            ng-hide="App.loading.isLoading('global')"
            ng-view
            news-scroll="#app-content"
            news-scroll-enabled-mark-read="Content.markReadEnabled()"
            news-scroll-auto-page="Content.autoPage()"
            news-scroll-mark-read="Content.scrollRead(itemIds)"></div>
</div>
