<?php

$feedid = isset($_['feedid']) ? $_['feedid'] : '';

$itemmapper = new OCA\News\ItemMapper();
$items = $itemmapper->findAll($feedid);

echo '<div id="feed_items">';
echo '<ul>';
foreach($items as $item) {
	
	if($item->isRead()){
		$newsItemClass = "read";
	} else {
		$newsItemClass = "";
	}
	
	if($item->isImportant()){
		$starClass = 'important';
		$startTitle = $l->t('Mark as unimportant');
	} else {
		$starClass = '';
		$startTitle = $l->t('Mark as important');
	}

	echo '<li class="news_item ' . $newsItemClass .'" data-id="' . $item->getId() . '" data-feedid="' . $feedid . '">';
		echo '<div class="item_utils">';
			echo '<ul>';
				echo '<li class="star ' . $starClass . '" title="' . $startTitle . '"></li>';
				echo '<li>' . parse_url($item->getUrl())['host'] . '</li>';
			echo '</ul>';
			echo '<ul class="hidden_item_utils">';
				echo '<li class="keep_unread">' . $l->t('Keep unread') . '<input type="checkbox" /></li>';
			echo '</ul>';
		echo '</div>';
		echo '<h1 class="item_title"><a target="_blank" href="' . $item->getUrl() . '">' . $item->getTitle() . '</a></h1>';
		echo '<div class="body">' . $item->getBody() . '</div>';
	echo '</li>';

	}
echo '</ul>';
echo '</div>';
