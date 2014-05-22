<?php
\OCP\Util::addScript('news', 'vendor/traceur-runtime/traceur-runtime');
\OCP\Util::addScript('news', 'vendor/angular/angular');
\OCP\Util::addScript('news', 'vendor/angular-route/angular-route');
\OCP\Util::addScript('news', 'vendor/angular-sanitize/angular-sanitize');
\OCP\Util::addScript('news', 'vendor/angular-animate/angular-animate');
\OCP\Util::addScript('news', 'vendor/momentjs/moment');
\OCP\Util::addScript('news', 'vendor/momentjs/min/langs');
\OCP\Util::addScript('news', 'vendor/bootstrap/tooltip');
\OCP\Util::addScript('news', 'build/app');

\OCP\Util::addStyle('news', 'bootstrap/tooltip');
\OCP\Util::addStyle('news', 'navigation');
\OCP\Util::addStyle('news', 'addnew');
\OCP\Util::addStyle('news', 'app');
\OCP\Util::addStyle('news', 'content');
\OCP\Util::addStyle('news', 'settings');
?>


<div id="app" ng-app="News" ng-cloak ng-controller="AppController as App">

	<div id="global-loading" class="loading-icon" ng-show="App.loading.isLoading('global')"></div>
	<!--
	<div id="undo-container">
		<div undo-notification id="undo">
			<a href="#"><?php p($l->t('Undo deletion of %s', '{{ getCaption() }}')); ?></a>
		</div>
	</div>
	<news-translate key="appName"><?php p($l->t('News')); ?></news-translate>
	-->

	<div id="app-navigation" ng-controller="NavigationController" ng-hide="App.loading.isLoading('global')">

		<ul class="with-icon" data-id="0" droppable>
			<?php //print_unescaped($this->inc('part.addnew')) ?>
			<?php //print_unescaped($this->inc('part.feed.unread')) ?>
			<?php //print_unescaped($this->inc('part.feed.starred')) ?>
			<?php //print_unescaped($this->inc('part.listfeed', ['folderId' => '0'])) ?>
			<?php //print_unescaped($this->inc('part.listfolder')) ?>
		</ul>

		<div id="app-settings" ng-controller="SettingsController as Settings">
			<?php print_unescaped($this->inc('part.settings')) ?>
		</div>

	</div>

	<script type="text/ng-template" id="content.html"><?php print_unescaped($this->inc('part.content')) ?></script>

	<div id="app-content" ng-hide="App.loading.isLoading('global')" ng-view></div>

	<!--
	<div id="app-content" ng-class="{
			loading: isLoading(),
			autopaging: isAutoPaging()
		}"
		ng-controller="ItemController"
		ng-show="initialized && !feedBusinessLayer.noFeeds()"
		news-item-scroll="true"
		item-shortcuts
		news-pull-to-refresh="loadNew()"
		tabindex="-1"
		news-auto-focus>
		<?php print_unescaped($this->inc("part.items")); ?>
	</div>

	-->

</div>
