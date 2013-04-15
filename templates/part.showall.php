<li ui-if="!feedBusinessLayer.isShowAll() && feedBusinessLayer.getNumberOfFeeds() > 0" class="show-all">
	<a ng-click="feedBusinessLayer.setShowAll(true)" href="#"><?php p($l->t('Show all')); ?></a>
</li>

<li ui-if="feedBusinessLayer.isShowAll() && feedBusinessLayer.getNumberOfFeeds() > 0" class="show-all">
	<a ng-click="feedBusinessLayer.setShowAll(false)" href="#"><?php p($l->t('Show only unread')); ?></a>
</li>
