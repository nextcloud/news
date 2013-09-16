<?php
\OCP\Util::addScript('news', 'vendor/angular/angular');
\OCP\Util::addScript('news', 'vendor/angular-ui/build/angular-ui');
\OCP\Util::addScript('news', 'vendor/momentjs/moment');
\OCP\Util::addScript('news', 'vendor/momentjs/min/langs');
\OCP\Util::addScript('appframework', 'vendor/bootstrap/tooltip');
\OCP\Util::addScript('appframework', 'public/app');
\OCP\Util::addScript('news', 'public/app');


\OCP\Util::addStyle('appframework', 'bootstrap/tooltip');
\OCP\Util::addStyle('news', 'addnew');
\OCP\Util::addStyle('news', 'feeds');
\OCP\Util::addStyle('news', 'items');
\OCP\Util::addStyle('news', 'settings');
\OCP\Util::addStyle('news', 'showall');
\OCP\Util::addStyle('news', 'firstrun');


// stylesheets for different OC versions
$version = \OCP\Util::getVersion();

// owncloud 6
if($version[0] > 5 || ($version[0] >= 5 && $version[1] >= 80)) {
	\OCP\Util::addStyle('news', 'owncloud6');
}


?>

<div id="app" ng-app="News" ng-cloak ng-controller="AppController">
	<div id="undo-container">
		<div undo-notification id="undo">
			<a href="#"><?php p($l->t('Undo deletion of %s', '{{ getCaption() }}')); ?></a>
		</div>
	</div>
	<div id="app-navigation" ng-controller="FeedController">
		<news-translate key="appName"><?php p($l->t('News')); ?></news-translate>

		<ul class="with-icon" data-id="0" droppable>
			<?php print_unescaped($this->inc('part.addnew')) ?>
			<?php print_unescaped($this->inc('part.feed.unread')) ?>
			<?php print_unescaped($this->inc('part.feed.starred')) ?>
			<?php print_unescaped($this->inc('part.listfeed', array('folderId' => '0'))) ?>
			<?php print_unescaped($this->inc('part.listfolder')) ?>
			<?php print_unescaped($this->inc('part.showall')); ?>
		</ul>

		<div id="app-settings" ng-controller="SettingsController"
			ng-class="{open: initialized && feedBusinessLayer.noFeeds()}">
			<?php print_unescaped($this->inc('part.settings')) ?>
		</div>

	</div>

	<div id="app-content" ng-class="{
			loading: isLoading(),
			autopaging: isAutoPaging()
		}"
		ng-controller="ItemController"
		ng-show="initialized && !feedBusinessLayer.noFeeds()"
		news-item-scroll="true"
		item-shortcuts
		news-pull-to-refresh="loadNew()"
		tabindex="-1">
		<?php print_unescaped($this->inc("part.items")); ?>
	</div>
	<div id="firstrun" ng-show="initialized && feedBusinessLayer.noFeeds()">
		<?php print_unescaped($this->inc("part.firstrun")); ?>
	</div>

</div>
