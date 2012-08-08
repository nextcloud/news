<?php

$l = new OC_l10n('news');

$child = isset($_['child']) ? $_['child'] : null;
$unreadItems = isset($_['unreadItems']) ? $_['unreadItems'] : null;
$favicon = $child->getFavicon();
if ($favicon == null) {
        $favicon = OCP\Util::imagePath('news', 'rss.svg');
}

echo '<li class="feed" data-id="' . $child->getId() . '">';
echo '<a href="#" style="background: url(' . $favicon . ') left center no-repeat; background-size:16px 16px;">' . $child->getTitle() .'</a>';
if ($unreadItems > 0) {
	echo '<span id="unreaditemcounter" class="nonzero">' . $unreadItems . '</span>';
}
else {
	echo '<span id="unreaditemcounter" class="zero"></span>';
}
echo '<button class="svg action" id="feeds_edit" title="' . $l->t('Edit feed') . '"></button>';
echo '<button class="svg action" id="feeds_delete" onClick="(News.Feed.delete(' . $child->getId(). '))" title="' . $l->t('Delete feed') . '"></button>';
echo '</li>';
