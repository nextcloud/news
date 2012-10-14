<?php

function print_collection_list($list, $lastViewedFeedId, $lastViewedFeedType) {

	foreach($list as $collection) {
		if ($collection instanceOf OCA\News\Folder) {
			$tmpl_folder = new OCP\Template("news", "part.listfolder");
			$tmpl_folder->assign('folder', $collection, false);
			$tmpl_folder->assign('lastViewedFeedId', $lastViewedFeedId);
			$tmpl_folder->assign('lastViewedFeedType', $lastViewedFeedType);
			$tmpl_folder->printpage();
			print_collection_list($collection->getChildren(), $lastViewedFeedId,
									$lastViewedFeedType);
			echo '</ul></li>';
		}
		elseif ($collection instanceOf OCA\News\Feed) { //onhover $(element).attr('id', 'newID');
			$itemmapper = new OCA\News\ItemMapper();

			$items = $itemmapper->findByFeedId($collection->getId());
			$counter = 0;
			foreach($items as $item) {
				if(!$item->isRead())
					++$counter;
			}
			$tmpl_feed = new OCP\Template("news", "part.listfeed");
			$tmpl_feed->assign('feed', $collection, false);
			$tmpl_feed->assign('unreadItemsCount',$counter);
			$tmpl_feed->assign('lastViewedFeedId', $lastViewedFeedId);
			$tmpl_feed->assign('lastViewedFeedType', $lastViewedFeedType);
			$tmpl_feed->printpage();
		}
		else {
		//TODO:handle error in this case
		}
	}

}

$allfeeds = isset($_['allfeeds']) ? $_['allfeeds'] : '';
$lastViewedFeedId = $_['lastViewedFeedId'];
$lastViewedFeedType = $_['lastViewedFeedType'];
$starredCount = $_['starredCount'];

?>

<li class="subscriptions <?php if($lastViewedFeedType == OCA\News\FeedType::SUBSCRIPTIONS) { echo "active"; }; ?>">
	<a class="title" href="#" ><?php echo $l->t('New articles'); ?></a>
	<span class="buttons">
    	<button class="svg action feeds_markread" title="<?php echo $l->t('Mark all read'); ?>"></button>
    </span>
</li>
<li class="starred <?php if($lastViewedFeedType == OCA\News\FeedType::STARRED) { echo "active"; }; ?>">
	<a class="title" href="#" ><?php echo $l->t('Starred'); ?></a>
	<span class="unread_items_counter"><?php echo $starredCount ?></span>
</li>

<?php
	print_collection_list($allfeeds, $lastViewedFeedId, $lastViewedFeedType);
