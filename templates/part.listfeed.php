<?php

$l = new OC_l10n('news');

$feed = isset($_['feed']) ? $_['feed'] : null;
$unreadItemsCount = isset($_['unreadItemsCount']) ? $_['unreadItemsCount'] : null;

$favicon = $feed->getFavicon();
if ($favicon == null) {
    $favicon = OCP\Util::imagePath('news', 'rss.svg');
}

if($unreadItemsCount == 0){
    $allReadClass = 'all_read';
} else {
    $allReadClass = '';
}

echo '<li class="feed" data-id="' . $feed->getId() . '">';
echo '<a href="#" style="background: url(' . $favicon . ') left center no-repeat; background-size:16px 16px;" class="' . $allReadClass . '">' . $feed->getTitle() .'</a>';
	echo '<span class="unreaditemcounter ' . $allReadClass . '">' . $unreadItemsCount . '</span>';
echo '<button class="svg action feeds_edit" title="' . $l->t('Edit feed') . '"></button>';
echo '<button class="svg action feeds_delete" onClick="(News.Feed.delete(' . $feed->getId(). '))" title="' . $l->t('Delete feed') . '"></button>';
echo '</li>';
