<?php

function print_collection_list($list) {
	
	foreach($list as $collection) {
		if ($collection instanceOf OCA\News\Folder){
			$tmpl_folder = new OCP\Template("news", "part.listfolder");
			$tmpl_folder->assign('folder', $collection);
			$tmpl_folder->printpage();
			print_collection_list($collection->getChildren());
			echo '</ul></li>';
		}
		elseif ($collection instanceOf OCA\News\Feed) { //onhover $(element).attr('id', 'newID');
			$itemmapper = new OCA\News\ItemMapper();

			$items = $itemmapper->findAll($collection->getId());
			$counter = 0;
			foreach($items as $item) {
				if(!$item->isRead())
					++$counter;
			}
			$tmpl_feed = new OCP\Template("news", "part.listfeed");
			$tmpl_feed->assign('feed', $collection);
			$tmpl_feed->assign('unreadItemsCount',$counter);
			$tmpl_feed->printpage();
		}
		else {
		//TODO:handle error in this case
		}
	}

}

$allfeeds = isset($_['allfeeds']) ? $_['allfeeds'] : '';
$feedId = $_['feedid'];

$itemMapper = new OCA\News\ItemMapper();
$unreadItemCountAll = $itemMapper->countEveryItemByStatus(OCA\News\StatusFlag::UNREAD);
$starredCount = $itemMapper->countEveryItemByStatus(OCA\News\StatusFlag::IMPORTANT);

switch ($feedId) {
	case -2:
		$subscriptionsClass = "selected_feed";
		$starredClass = "";
		break;

	case -1:
		$subscriptionsClass = "";
		$starredClass = "selected_feed";
		break;
	
	default:
		$subscriptionsClass = "";
		$starredClass = "";
		break;
}

if($unreadItemCountAll > 0){
	$allUnreadItemClass = "";
} else {
	$allUnreadItemClass = "all_read";
}

if($starredCount > 0){
	$starredCountClass = "";
} else {
	$starredCountClass = "all_read";
}

?>

<li data-id="-2" class="subscriptions folder <?php echo $allUnreadItemClass ?>" id="<?php echo $subscriptionsClass ?>">
	<a href="#" ><?php echo $l->t('New articles'); ?></a>
	<span class="unreaditemcounter"><?php echo $unreadItemCountAll ?></span>
</li>
<li data-id="-1" class="starred folder <?php echo $starredCountClass ?>" id="<?php echo $starredClass ?>">
	<a href="#" ><?php echo $l->t('Starred'); ?></a>
	<span class="unreaditemcounter"><?php echo $starredCount ?></span>
</li>

<?php
	print_collection_list($allfeeds);
