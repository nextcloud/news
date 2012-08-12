<?php

$l = new OC_l10n('news');

$feed = isset($_['feed']) ? $_['feed'] : null;
$unreadItems = isset($_['unreadItems']) ? $_['unreadItems'] : null;
$favicon = $feed->getFavicon();
if ($favicon == null) {
        $favicon = OCP\Util::imagePath('news', 'rss.svg');
}

echo '<li class="feed" data-id="' . $feed->getId() . '">';
echo '<a href="#" style="background: url(' . $favicon . ') left center no-repeat; background-size:16px 16px;" class="' . 
      (($unreadItems > 0) ? 'nonzero' : 'zero') . '">' . $feed->getTitle() .'</a>';
if ($unreadItems > 0) {
	echo '<span class="unreaditemcounter nonzero">' . $unreadItems . '</span>';
}
else {
	echo '<span class="unreaditemcounter zero"></span>';
}
echo '<button class="svg action feeds_edit" title="' . $l->t('Edit feed') . '"></button>';
echo '<button class="svg action feeds_delete" onClick="(News.Feed.delete(' . $feed->getId(). '))" title="' . $l->t('Delete feed') . '"></button>';
echo '</li>';
