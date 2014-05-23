<div id="first-run" ng-if="App.isFirstRun()">
    <h1><?php p($l->t('Welcome to the ownCloud News app!')) ?></h1>
</div>

<div news-auto-focus="#app-content" ng-if="!App.isFirstRun()">
    <ul ng-if="isCompactView()">

    </ul>
    <ul ng-if="!isCompactView()">

    </ul>
</div>
