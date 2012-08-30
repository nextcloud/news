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

?>

<li class="subscriptions <?php if($feedId === -2){ echo "selected_feed"; }; ?>">
	<a class="title" href="#" ><?php echo $l->t('New articles'); ?></a>
	<span class="unread_items_counter"><?php echo $unreadItemCountAll ?></span>
	<span class="buttons">
    	<button class="svg action feeds_markread" title="<?php echo $l->t('Mark all read'); ?>"></button>
    </span>
</li>
<li class="starred <?php if($feedId === -1){ echo "selected_feed"; }; ?>">
	<a class="title" href="#" ><?php echo $l->t('Starred'); ?></a>
	<span class="unread_items_counter"><?php echo $starredCount ?></span>
	<span class="buttons">
    	<button class="svg action feeds_markread" title="<?php echo $l->t('Mark all read'); ?>"></button>
    </span>
</li>

<?php
	print_collection_list($allfeeds);
