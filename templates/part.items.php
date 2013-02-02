<ul>
	<li class="feed_item"
		ng-repeat="item in getItems(activeFeed.type, activeFeed.id) | orderBy:'date':true "
		ng-class="{read: item.isRead}"
		data-id="{{item.id}}"
    	data-feed="{{item.feedId}}">
		<h2 class="item_date">
			<time class="timeago" datetime="">{{item.getRelativeDate()}}</time>
		</h2>
		
		<div class="utils">
			<ul class="primary_item_utils">
				<li ng-class="{important: item.isImportant}"
					ng-click="toggleImportant(item.id)"
					class="star" 
					title="{{item.isImportant}}">
				</li>
			</ul>
		</div>

		<h1 class="item_title">
			<a ng-click="markRead(item.id, item.feedId)" 
				target="_blank" href="{{item.url}}">{{item.title}}</a>
		</h1>

		<h2 class="item_author">from 
			<a href="#" 
				ng-click="loadFeed(item.feedId)"
				class="from_feed">{{item.feedTitle}}</a> {{item.getAuthorLine()}}
		</h2>

		<div class="enclosure" ng-show="item.enclosure">
			<audio controls="controls"><source ng-src="{{item.enclosure.link}}" type="{{item.enclosure.type}}"></source></audio>
		</div>
		
		<div class="body" 
				ng-click="markRead(item.id, item.feedId)" 
				ng-bind-html-unsafe="item.body">
		</div>

		<div class="bottom_utils">
			<ul class="secondary_item_utils"
				ng-class="{show_keep_unread: isKeptUnread(item.id)}">
				<li class="share_link">
					<a class="share" data-item-type="news_item" 
					   data-item="{{item.id}}" title="<?php p($l->t('Share')) ?>" 
					   data-possible-permissions="<?php //p((OCP\Share::PERMISSION_READ | OCP\Share::PERMISSION_SHARE)) ?>" 
					   href="#">
					   <?php p($l->t('Share')) ?>
		  			</a>
		  		</li>
				<li ng-click="keepUnread(item.id, item.feedId)" 
					class="keep_unread"><?php p($l->t('Keep unread')); ?>
					<input type="checkbox" ng-checked="isKeptUnread(item.id)"/>
				</li>
			</ul>
		</div>
	</li>
</ul>
