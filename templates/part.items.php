<?php

$feedid = isset($_['feedid']) ? $_['feedid'] : '';

$itemmapper = new OC_News_ItemMapper();

$items = $itemmapper->findAll($feedid);

echo '<ul class="accordion">';
foreach($items as $item) {
	echo '<li><div class="title">' . $item->getTitle() . '</div>';
	echo '<div class="body">' . $item->getBody() . '</div></li>';
}
echo '</ul>';
