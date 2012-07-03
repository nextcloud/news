<?php

$feedid = isset($_['feedid']) ? $_['feedid'] : '';

$itemmapper = new OC_News_ItemMapper();

$items = $itemmapper->findAll($feedid);

echo '<ul>';
foreach($items as $item) {
	echo '<li>' . $item->getTitle() . '</li>';
}
echo '</ul>';
