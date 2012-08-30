<?php

$l = new OC_l10n('news');

$feed = isset($_['feed']) ? $_['feed'] : null;
$unreadItemsCount = isset($_['unreadItemsCount']) ? $_['unreadItemsCount'] : null;

$favicon = $feed->getFavicon();
if ($favicon == null) {
    $favicon = OCP\Util::imagePath('core', 'actions/public.svg');
}

echo '<li class="feed" data-id="' . $feed->getId() . '">';
    echo '<a style="background-image: url(' . $favicon . ');" href="#" class="title">' . htmlspecialchars_decode($feed->getTitle()) .'</a>';
	echo '<span class="unread_items_counter">' . $unreadItemsCount . '</span>';
    echo '<span class="buttons">';
        echo '<button class="svg action feeds_delete" title="' . $l->t('Delete feed') . '"></button>';
        echo '<button class="svg action feeds_markread" title="' . $l->t('Mark all read') . '"></button>';
    echo '</span>';
echo '</li>';
