<?php
script('news', 'vendor/traceur-runtime/traceur-runtime.min');
script('news', 'vendor/angular/angular.min');
script('news', 'vendor/angular-route/angular-route.min');
script('news', 'vendor/angular-sanitize/angular-sanitize.min');
script('news', 'vendor/angular-animate/angular-animate.min');
script('news', 'vendor/momentjs/min/moment-with-locales.min');
script('news', 'build/app.min');

style('news', 'app');
style('news', 'navigation');
style('news', 'content');
style('news', 'settings');
?>


<div id="app" ng-app="News" ng-cloak ng-controller="AppController as App">

	<div id="global-loading" class="icon-loading" ng-show="App.loading.isLoading('global')"></div>

	<!-- navigation -->
	<div id="app-navigation" ng-controller="NavigationController as Navigation" ng-hide="App.loading.isLoading('global')">
		<news-title-unread-count unread-count="{{ Navigation.getUnreadCount() }}"></news-title-unread-count>

		<ul class="with-icon" data-folder-id="0" news-droppable>
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

	<div id="app-content">
		<div id="app-content-wrapper"
			ng-class="{
				'icon-loading': App.loading.isLoading('content'),
				'autopaging': App.loading.isLoading('autopaging')
			}"
			ng-hide="App.loading.isLoading('global')"
			ng-view
			tabindex="-1"
			news-auto-focus
			news-scroll
			news-scroll-enabled-auto-page="Content.autoPagingEnabled()"
			news-scroll-enabled-mark-read="Content.markReadEnabled()"
			news-scroll-auto-page="Content.autoPage()"
			news-scroll-mark-read="Content.scrollRead(itemIds)">

		</div>
</div>
