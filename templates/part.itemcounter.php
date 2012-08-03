<?php

function countUnreadItems($feedid) {
	$itemmapper = new OC_News_ItemMapper();

	$items = $itemmapper->findAll($feedid);
	$counter = 0;
	foreach($items as $item) {
		if(!$item->isRead())
			++$counter;
	}
	if ($counter > 0) {
		echo '<span id="unreaditemcounter" class="nonzero">' . $counter . '</span>';
	}
	else {
		echo '<span id="unreaditemcounter" class="zero"></span>';
	}
}