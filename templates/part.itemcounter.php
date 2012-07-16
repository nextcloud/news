<?php

function countUnreadItems($feedid) {
	$itemmapper = new OC_News_ItemMapper();

	$items = $itemmapper->findAll($feedid);
	$counter = 0;
	foreach($items as $item) {
		if(!$item->isRead())
			++$counter;
	}
	echo '<span id="unreaditemcounter">' . $counter . '</span>';
}

?>