<?php

$feedid = isset($_['feedid']) ? $_['feedid'] : '';

$itemmapper = new OC_News_ItemMapper();

$items = $itemmapper->findAll($feedid);

echo '<ul class="accordion">';
foreach($items as $item) {
	$title = $item->getTitle();
	echo '<li>';
	if ($item->isRead()) {
		echo '<div class="title_read">' . $title . '</div>';
	}
	else {
		echo '<div class="title_unread" onClick="News.Feed.markItem(' . $item->getId() . ')">' . $title . '</div>';
	}
	echo '<div class="body">' . $item->getBody() . '</div></li>';
}
echo '</ul>';
