<?php
\OCP\Util::addScript('news', 'vendor/traceur-runtime/traceur-runtime.min');
\OCP\Util::addScript('news', 'vendor/angular/angular.min');
\OCP\Util::addScript('news', 'vendor/angular-route/angular-route.min');
\OCP\Util::addScript('news', 'vendor/angular-sanitize/angular-sanitize.min');
\OCP\Util::addScript('news', 'vendor/angular-animate/angular-animate.min');
\OCP\Util::addScript('news', 'vendor/momentjs/min/moment-with-langs.min');
\OCP\Util::addScript('news', 'vendor/bootstrap/tooltip');
\OCP\Util::addScript('news', 'build/app');

\OCP\Util::addStyle('news', 'bootstrap/tooltip');
\OCP\Util::addStyle('news', 'app');
\OCP\Util::addStyle('news', 'navigation');
\OCP\Util::addStyle('news', 'content');
\OCP\Util::addStyle('news', 'settings');
?>


<div id="app" ng-app="News" ng-cloak ng-controller="AppController as App">

	<div id="global-loading" class="icon-loading" ng-show="App.loading.isLoading('global')"></div>

	<!-- navigation -->
	<div id="app-navigation" ng-controller="NavigationController as Navigation" ng-hide="App.loading.isLoading('global')">
		<news-title-unread-count unread-count="{{ Navigation.getUnreadCount() }}"></news-title-unread-count>

		<ul class="with-icon" data-folder-id="0" news-droppable>
			<?php print_unescaped($this->inc('part.addnew')) ?>
			<?php print_unescaped($this->inc('part.feed.unread')) ?>
			<?php print_unescaped($this->inc('part.feed.starred')) ?>
			<?php print_unescaped($this->inc('part.listfeed', ['folderId' => '0'])) ?>
			<?php print_unescaped($this->inc('part.listfolder')) ?>
		</ul>

		<!-- settigns -->
		<div id="app-settings" ng-controller="SettingsController as Settings">
			<?php print_unescaped($this->inc('part.settings')) ?>
		</div>

	</div>

	<!-- content -->
	<script type="text/ng-template" id="content.html"><?php print_unescaped($this->inc('part.content')) ?></script>

	<div id="app-content"
		ng-class="{
			'icon-loading': App.loading.isLoading('content'),
			'autopaging': App.loading.isLoading('autopaging')
		}"
		ng-hide="App.loading.isLoading('global')"
		ng-view
		tabindex="-1"
		news-scroll
		news-scroll-enabled-auto-page="Content.autoPagingEnabled()"
		news-scroll-enabled-mark-read="Content.markReadEnabled()"
		news-scroll-auto-page="Content.autoPage()"
		news-scroll-mark-read="Content.scrollRead(itemIds)"></div>

</div>
