<li ui-if="!feedBusinessLayer.isShowAll() && feedBusinessLayer.getNumberOfFeeds() > 0"
	class="show-all">
	<a ng-click="feedBusinessLayer.setShowAll(true)"
		href="#"
		news-click-scroll="{direction: 'down', scrollArea: '#app-navigation > ul'}"><?php p($l->t('Show read articles')); ?></a>
</li>

<li ui-if="feedBusinessLayer.isShowAll() && feedBusinessLayer.getNumberOfFeeds() > 0"
	class="show-all">
	<a ng-click="feedBusinessLayer.setShowAll(false)"
		href="#"><?php p($l->t('Hide read articles')); ?></a>
</li>
