<?php

$l = new OC_l10n('news');

if(isset($_['mock'])) {
    $feedTitle = '';
    $feedId = -1;
    $unreadItemsCount = -1;
    $favicon = OCP\Util::imagePath('core', 'actions/public.svg');
} else {
    $feed = isset($_['feed']) ? $_['feed'] : null;
    $feedTitle = $feed->getTitle();
    $feedId =  $feed->getId();
    $unreadItemsCount = isset($_['unreadItemsCount']) ? $_['unreadItemsCount'] : null;
    $favicon = $feed->getFavicon();
    if ($favicon == null) {
        $favicon = OCP\Util::imagePath('core', 'actions/public.svg');
    }
}

echo '<li class="feed" data-id="' . $feedId . '">';
    echo '<a style="background-image: url(' . $favicon . ');" href="#" class="title">' . htmlspecialchars($feedTitle, ENT_QUOTES, 'UTF-8') .'</a>';
	echo '<span class="unread_items_counter">' . $unreadItemsCount . '</span>';
    echo '<span class="buttons">';
        echo '<button class="svg action feeds_delete" title="' . $l->t('Delete feed') . '"></button>';
        echo '<button class="svg action feeds_markread" title="' . $l->t('Mark all read') . '"></button>';
    echo '</span>';
echo '</li>';
