<li ui-if="!isShowAll()" class="show-all">
	<a ng-click="setShowAll(true)" href="#"><?php p($l->t('Show all')); ?></a>
</li>

<li ui-if="isShowAll()" class="show-all">
	<a ng-click="setShowAll(false)" href="#"><?php p($l->t('Show only unread')); ?></a>
</li>
