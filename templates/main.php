<div id="app" ng-app="News">
	<div id="left-content" ng-controller="FeedController">

		<ul class="with-icon" data-id="0" droppable>
			<?php print_unescaped($this->inc('part.addnew')) ?>
			<?php print_unescaped($this->inc('part.feed.unread')) ?>
			<?php print_unescaped($this->inc('part.feed.starred')) ?>
			<?php print_unescaped($this->inc('part.listfolder')) ?>
			<?php print_unescaped($this->inc('part.listfeed', array('folderId' => '0'))) ?>
			<?php print_unescaped($this->inc('part.showall')); ?>
		</ul>

                <div id="app-settings" ng-controller="SettingsController">
			<div id="app-settings-header">
				<button name="app settings" 
						class="settings-button"
                                                click-slide-toggle="{
                                                        selector: '#app-settings-content',
                                                        hideOnFocusLost: true
                                                }"></button>
			</div>
			<div id="app-settings-content">
				<?php print_unescaped($this->inc('part.settings')) ?>
			</div>
		</div>

	</div>

	<div id="right-content" ng-class="{loading: loading.loading>0}"
		ng-controller="ItemController" 	when-scrolled="scroll()" feed-navigation>
		<?php print_unescaped($this->inc("part.items")); ?>
	</div>
	
</div>
