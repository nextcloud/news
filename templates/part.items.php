<?php

$feedId = isset($_['feedid']) ? $_['feedid'] : '';

$itemMapper = new OCA\News\ItemMapper();

$showAll = OCP\Config::getUserValue(OCP\USER::getUser(), 'news', 'showAll'); 

// select items by feed id and by preference
switch ($feedId) {
    case -1:
    	$feedMapper = new OCA\News\FeedMapper();
    	$items = $itemMapper->findEveryItemByStatus(OCA\News\StatusFlag::IMPORTANT);
        break;

    case -2:
        $items = $itemMapper->findEveryItemByStatus(OCA\News\StatusFlag::UNREAD);
        break;
    
    default:
    	if($showAll){
    		$items = $itemMapper->findAll($feedId);
        } else {
        	$items = $itemMapper->findAllStatus($feedId, OCA\News\StatusFlag::UNREAD);
        }
        break;
}

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

	echo '<li class="feed_item ' . $newsItemClass .'" data-id="' . $item->getId() . '" data-feedid="' . $feedId . '">';

		echo '<h2 class="item_date"><time class="timeago" datetime="' . 
			date('c', $item->getDate()) . '">' . date('F j, Y, g:i a', $item->getDate()) .  '</time>' . '</h2>';

		echo '<div class="utils">';
			echo '<ul class="primary_item_utils">';
				echo '<li class="star ' . $starClass . '" title="' . $startTitle . '"></li>';
			echo '</ul>';
		echo '</div>';

		echo '<h1 class="item_title"><a target="_blank" href="' . $item->getUrl() . '">' . $item->getTitle() . '</a></h1>';	

		if(trim($item->getAuthor()) == ''){
			$from = $l->t('from') . ' ' . parse_url($item->getUrl(), PHP_URL_HOST);
		} else {
			$from = $l->t('from') . ' ' . $item->getAuthor();
		}
		echo '<h2 class="item_author">' . $from . '</h2>';

		echo '<div class="body">' . $item->getBody() . '</div>';

		echo '<div class="bottom_utils">';
			echo '<ul class="secondary_item_utils">';
				echo '<li class="keep_unread">' . $l->t('Keep unread') . '<input type="checkbox" /></li>';
			echo '</ul>';
		echo '</div>';

	echo '</li>';

	}
echo '</ul>';
