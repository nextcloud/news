<?php

$feedid = isset($_['feedid']) ? $_['feedid'] : '';

$itemmapper = new OC_News_ItemMapper();
$items = $itemmapper->findAll($feedid);

echo '<div id="feed_items">';
echo '<ul>';
foreach($items as $item) {
	if($item->isRead()){
		$readClass = "title_read";
	} else {
		$readClass = "title_unread";
	}

	echo '<li class="news_item ' . $readClass .'" data-id="' . $item->getId() . '" data-feedid="' . $feedid . '">';
	echo '<h1 class="item_title"><a href="' . $item->getUrl() . '">' . $item->getTitle() . '</a></h1>';
	echo '<div class="body">' . $item->getBody() . '</div>';
	echo '</li>';

	}
echo '</ul>';
echo '</div>';