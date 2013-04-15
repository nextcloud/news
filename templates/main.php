<?php
\OCP\Util::addScript('news', 'vendor/momentjs/moment');
\OCP\Util::addScript('news', 'vendor/momentjs/langs');

\OCP\Util::addScript('appframework', 'vendor/angular/angular');
\OCP\Util::addScript('appframework', 'public/app');

\OCP\Util::addScript('news', 'vendor/md5js/md5');
\OCP\Util::addScript('news', 'vendor/angular-ui/angular-ui');

\OCP\Util::addScript('news', 'public/app');

\OCP\Util::addStyle('news', 'addnew');
\OCP\Util::addStyle('news', 'feeds');
\OCP\Util::addStyle('news', 'items');
\OCP\Util::addStyle('news', 'settings');
\OCP\Util::addStyle('news', 'addnew');
\OCP\Util::addStyle('news', 'showall');

?>

<div id="app" ng-app="News">
	<div id="app-navigation" ng-controller="FeedController">

		<ul class="with-icon" data-id="0" droppable>
			<?php print_unescaped($this->inc('part.addnew')) ?>
			<?php print_unescaped($this->inc('part.feed.unread')) ?>
			<?php print_unescaped($this->inc('part.feed.starred')) ?>
			<?php print_unescaped($this->inc('part.listfolder')) ?>
			<?php print_unescaped($this->inc('part.listfeed', array('folderId' => '0'))) ?>
			<?php print_unescaped($this->inc('part.showall')); ?>
		</ul>

		<div id="app-settings" ng-controller="SettingsController">
			<?php print_unescaped($this->inc('part.settings')) ?>
		</div>

	</div>

	<div id="app-content" ng-class="{loading: isLoading()}"
		ng-controller="ItemController" 	when-scrolled="scroll()" feed-navigation>
		<?php print_unescaped($this->inc("part.items")); ?>
	</div>
	
</div>
