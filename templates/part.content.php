<div id="first-run" ng-if="App.isFirstRun()">
    <h1><?php p($l->t('Welcome to the ownCloud News app!')) ?></h1>
</div>

<div ng-if="!App.isFirstRun()">
    <ul ng-if="Content.isCompactView()">
        <?php print_unescaped($this->inc('part.content.compact')); ?>
    </ul>

    <ul ng-if="!Content.isCompactView()">
        <?php print_unescaped($this->inc('part.content.expand')); ?>
    </ul>
</div>
