<?php
script('news', [
    'vendor/es6-shim/es6-shim.min',
    'vendor/angular/angular.min',
    'vendor/angular-route/angular-route.min',
    'vendor/angular-sanitize/angular-sanitize.min',
    'vendor/angular-animate/angular-animate.min',
    'vendor/momentjs/min/moment-with-locales.min',
    'build/app',
]);

style('news', [
    'app',
    'navigation',
    'content',
    'settings',
    'custom'
]);
?>


<div id="app" ng-app="News" ng-cloak ng-controller="AppController as App">

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

    <div id="app-content"
        ng-class="{
            'loading-content': App.loading.isLoading('content') && !App.loading.isLoading('global'),
            'first-run': App.isFirstRun()
        }"
        tabindex="-1">
        <div id="app-content-wrapper"
            ng-class="{'autopaging': App.loading.isLoading('autopaging')}"
            ng-hide="App.loading.isLoading('global')"
            ng-view
            news-scroll
            news-scroll-enabled-auto-page="Content.autoPagingEnabled()"
            news-scroll-enabled-mark-read="Content.markReadEnabled()"
            news-scroll-auto-page="Content.autoPage()"
            news-scroll-mark-read="Content.scrollRead(itemIds)"></div>
</div>
