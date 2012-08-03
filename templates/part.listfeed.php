<?php

// FIXME: somehow gets included twice
include_once("part.itemcounter.php");

$l = new OC_l10n('news');

$child = isset($_['child']) ? $_['child'] : null;
$favicon = $child->getFavicon();
if ($favicon == null) {
        $favicon = OCP\Util::imagePath('news', 'rss.svg');
}

echo '<li class="feeds_list" data-id="' . $child->getId() . '"><a href="' . OCP\Util::linkTo('news', 'index.php'). '?feedid=' . $child->getId() . '" style="background: url(' . $favicon . ') left center no-repeat; background-size:16px 16px;">' . $child->getTitle() .'</a>';
countUnreadItems($child->getId());
echo '<button class="svg action" id="feeds_delete" onClick="(News.Feed.delete(' . $child->getId(). '))" title="' . $l->t('Delete feed') . '"></button>';
echo '<button class="svg action" id="feeds_edit" title="' . $l->t('Edit feed') . '"></button>';
echo '</li>';
