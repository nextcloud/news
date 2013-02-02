<div class="content_wrapper" ng-app="News">
	<div id="leftcontent_news" class="main_column">
		<div id="feed_wrapper">
			<div id="feeds" ng-controller="FeedController">
				<ul data-id="0" droppable>
					<li ng-class="{
							active: isFeedActive(feedType.Subscriptions, 0),
							all_read: getUnreadCount(feedType.Subscriptions, 0)==0
						}" 
					    class="subscriptions"
					    ng-show="isShown(feedType.Subscriptions, 0)">
						<a class="title" 
						   href="#" 
						   ng-click="loadFeed(feedType.Subscriptions, 0)">
						   <?php p($l->t('New articles'))?>
						</a>
						<span class="unread_items_counter">
							{{ getUnreadCount(feedType.Subscriptions, 0) }}
						</span>
						<span class="buttons">
					    	<button class="svg action feeds_markread" 
					    			ng-click="markAllRead(feedType.Subscriptions, 0)"
					    	        title="<?php p($l->t('Mark all read')) ?>"></button>
					    </span>
					</li>
					<li ng-class="{
							active: isFeedActive(feedType.Starred, 0),
							all_read: getUnreadCount(feedType.Starred, 0)==0
						}" 
					    class="starred"
					    ng-show="isShown(feedType.Starred, 0)">
						<a class="title" 
						   href="#"
						   ng-click="loadFeed(feedType.Starred, 0)">
						   <?php p($l->t('Starred')) ?>
						</a>
						<span class="unread_items_counter">
							{{ getUnreadCount(feedType.Starred, 0) }}
						</span>
					</li>

					<?php print_unescaped($this->inc('part.listfolder')) ?>
					<?php print_unescaped($this->inc('part.listfeed', array('folderId' => '0'))) ?>

				</ul>
			</div>
		</div>

		<?php print_unescaped($this->inc('part.settings')) ?>

	</div>

	<div id="rightcontent_news" class="main_column">
		<div id="feed_items" 
				ng-class="{loading: loading.loading>0}"
				ng-controller="ItemController" 
				when-scrolled="scroll()"
				feed-navigation>
			<?php
				print_unescaped($this->inc("part.items"));
			?>
		</div>
	</div>
</div>
