<?php 

$itemmapper = new OCA\News\ItemMapper();

$items = $itemmapper->findAllStatus(155, OCA\News\StatusFlag::Unread);

foreach ($items as $item) {
	echo $item->getTitle();
}