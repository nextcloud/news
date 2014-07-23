<div class="pull-refresh" ng-class="{refresh: refresh}"></div>

<ul>
	<li class="feed_item"

		ng-repeat="item in itemBusinessLayer.getAll() | orderBy:['-id'] "
		ng-class="{ read: item.isRead(), compact: isCompactView(), open: item.active}"
		data-id="{{ item.id }}"
		ng-click="itemBusinessLayer.setRead(item.id)">

		<div class="item_heading">
			<button ng-class="{ important: item.isStarred() }"
					ng-click="itemBusinessLayer.toggleStarred(item.id)"
					class="star"
					>
			</button>
			<a class="external" 
				target="_blank" 
				ng-href="{{ item.url }}" 
				title="<?php p($l->t('read on website')) ?>">
			</a>
			<span class="timeago" title="{{item.pubDate*1000|date:'dd-MM-yyyy'}}">
				{{ getRelativeDate(item.pubDate) }}
			</span>
			<h1>
				<a ng-click="item.active = !item.active" href="#">{{ item.title }}</a>
			</h1>
		</div>
		
		<h2 class="item_date">
			<span class="timeago" title="{{item.pubDate*1000|date:'dd-MM-yyyy'}}">
				{{ getRelativeDate(item.pubDate) }}
			</span>
		</h2>

		<div class="item_utils">
			<ul class="primary_item_utils">
				<li>
					<button 
					title="<?php p($l->t('star')) ?>"
					ng-class="{ important: item.isStarred() }"
					ng-click="itemBusinessLayer.toggleStarred(item.id)"
					class="star"></button>
				</li>
			</ul>
		</div>

		<h1 class="item_title">
			<a target="_blank" ng-href="{{ item.url }}">
				{{ item.title }}
			</a>
		</h1>

		<h2 class="item_author">
			<span ng-show="itemBusinessLayer.noFeedActive() && feedBusinessLayer.getFeedLink(item.feedId)">
				<?php p($l->t('from')) ?>
				<a 	target="_blank" ng-href="{{ feedBusinessLayer.getFeedLink(item.feedId) }}"
					class="from_feed">{{ itemBusinessLayer.getFeedTitle(item.id) }}</a>
			</span>
			<span ui-if="item.author">
				<?php p($l->t('by')) ?>
				{{ item.author }}
			</span>
	</h2>

		<div class="enclosure" ui-if="item.enclosureLink" ng-switch="item.enclosureMime.split('/')[0]">
			<div ng-switch-when="audio">
				<news-audio type="{{ item.enclosureMime }}" ng-src="{{ item.enclosureLink|trustUrl }}"><?php
					p($l->t('Download'))
				?></news-audio>
			</div>
			<div ng-switch-when="video">
				<news-video type="{{ item.enclosureMime }}" ng-src="{{ item.enclosureLink|trustUrl }}"><?php
					p($l->t('Download'))
				?></news-video>
			</div>
			<img ng-switch-when="image" type="{{ item.enclosureMime }}" ng-src="{{ item.enclosureLink|trustUrl }}" alt="" />
			<a ng-switch-default type="{{ item.enclosureMime }}" ng-href="{{ item.enclosureLink|trustUrl }}"><?php
				p($l->t('Download'))
			?></a>
		</div>

		<div class="item_body" news-bind-html-unsafe="item.body">
		</div>

		<div class="item_bottom_utils">
			<ul class="secondary_item_utils"
				ng-class="{ show_keep_unread: itemBusinessLayer.isKeptUnread(item.id) }">
				<li ng-click="itemBusinessLayer.toggleKeepUnread(item.id)"
					class="keep_unread"><?php p($l->t('Keep unread')); ?>
					<input type="checkbox" ng-checked="itemBusinessLayer.isKeptUnread(item.id)"/>
				</li>
			</ul>
		</div>
	</li>
</ul>
