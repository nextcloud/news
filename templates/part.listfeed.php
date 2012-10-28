<?php

require_once \OC_App::getAppPath('news') . '/lib/feedtypes.php';


$l = new OC_l10n('news');


$feed = isset($_['feed']) ? $_['feed'] : null;

$feedTitle = $feed->getTitle();
$feedId =  $feed->getId();
$unreadItemsCount = isset($_['unreadItemsCount']) ? $_['unreadItemsCount'] : null;
$favicon = $feed->getFavicon();

if ($favicon == null) {
    $favicon = OCP\Util::imagePath('core', 'actions/public.svg');
}

$lastViewedFeedId = isset($_['lastViewedFeedId']) ? $_['lastViewedFeedId'] : null;
$lastViewedFeedType = isset($_['lastViewedFeedType']) ? $_['lastViewedFeedType'] : null;

if ($lastViewedFeedType == OCA\News\FeedType::FEED && $lastViewedFeedId == $feedId){
    $activeClass = 'active';
} else {
    $activeClass = '';
}

echo '<li class="feed ' . $activeClass . '" data-id="' . $feedId . '">';
    echo '<a style="background-image: url(' . $favicon . ');" href="#" class="title">' . $feedTitle .'</a>';
	echo '<span class="unread_items_counter">' . $unreadItemsCount . '</span>';
    echo '<span class="buttons">';
        echo '<button class="svg action feeds_delete" title="' . $l->t('Delete feed') . '"></button>';
        echo '<button class="svg action feeds_markread" title="' . $l->t('Mark all read') . '"></button>';
    echo '</span>';
echo '</li>';
