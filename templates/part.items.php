<ul>
	<li class="feed_item"
		ng-repeat="item in itemBl.getAll() | orderBy:'id':true "
		ng-class="{ read: item.isRead() }"
		data-id="{{ item.id }}"
    	data-feed="{{ item.feedId }}">
		<h2 class="item_date">
			<time class="timeago" datetime="">{{ item.getRelativeDate() }}</time>
		</h2>
		
		<div class="utils">
			<ul class="primary_item_utils">
				<li ng-class="{ important: item.isStarred() }"
					ng-click="itemBl.toggleStarred(item.id)"
					class="star" 
					title="<?php p($l->t('Save for later')) ?>">
				</li>
			</ul>
		</div>

		<h1 class="item_title">
			<a ng-click="itemBl.setRead(item.id)" 
				target="_blank" href="{{ item.url }}">{{ item.title }}</a>
		</h1>

		<h2 class="item_author">
			<span ng-show="itemBl.noFeedActive() && feedBl.getFeedLink(item.feedId)">
				<?php p($l->t('from')) ?>
				<a 	target="_blank" href="{{ feedBl.getFeedLink(item.feedId) }}"
					class="from_feed">{{ itemBl.getFeedTitle(item.id) }}</a> 
			</span>
			<span ui-if="item.author">
				<?php p($l->t('by')) ?>
				{{ item.author }}
			</span>
	</h2>

		<div class="enclosure" ui-if="item.enclosureLink">
			<audio controls="controls" ng-src="{{ item.enclosureLink }}" 
					type="{{ item.enclosureType }}">
				<?php p($l->t('Cant play audio format')) ?> {{item.enclosureType}}
			</audio>
		</div>
		
		<div class="body" 
				ng-click="itemBl.setRead(item.id)" 
				ng-bind-html-unsafe="item.body">
		</div>

		<div class="bottom_utils">
			<ul class="secondary_item_utils"
				ng-class="{ show_keep_unread: itemBl.isKeptUnread(item.id) }">
				<li ng-click="itemBl.toggleKeepUnread(item.id)" 
					class="keep_unread"><?php p($l->t('Keep unread')); ?>
					<input type="checkbox" ng-checked="itemBl.isKeptUnread(item.id)"/>
				</li>
			</ul>
		</div>
	</li>
</ul>
