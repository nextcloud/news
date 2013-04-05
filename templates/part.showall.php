<li ui-if="!feedBl.isShowAll() && feedBl.getNumberOfFeeds() > 0" class="show-all">
	<a ng-click="feedBl.setShowAll(true)" href="#"><?php p($l->t('Show all')); ?></a>
</li>

<li ui-if="feedBl.isShowAll() && feedBl.getNumberOfFeeds() > 0" class="show-all">
	<a ng-click="feedBl.setShowAll(false)" href="#"><?php p($l->t('Show only unread')); ?></a>
</li>
