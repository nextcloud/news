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
			<span ng-if="item.author">
				<?php p($l->t('by')) ?>
				{{ item.author }}
			</span>
		</h2>

		<div class="enclosure" ng-if="item.enclosureLink">
			<news-audio type="{{ item.enclosureType }}" ng-src="{{ item.enclosureLink|trustUrl }}"><?php
				p($l->t('Download'))
			?></news-audio>
		</div>

		<div class="body" news-bind-html-unsafe="item.body">
		</div>

		<div class="bottom-utils">
			<ul ng-show="item.keepUnread">
				<li ng-click="Content.toggleKeepUnread(item.id)">
					<label for="keep-unread">
                        <input type="checkbox" name="keep-unread" ng-checked="item.keepUnread"/>
                        <?php p($l->t('Keep unread')); ?>
                    </label>
				</li>
			</ul>
		</div>
	</li>
</ul>
