<?php 

$feedmapper = new OC_News_FeedMapper();
$foldermapper = new OC_News_FolderMapper();

$folder = new OC_News_Folder( 'Friends' );
$folderid = $foldermapper->insert($folder);

$feed = OC_News_Utils::fetch( 'http://algorithmsforthekitchen.com/blog/?feed=rss2' );
echo '<br>' . $feed->getTitle() . '<br>';

$feedmapper->insert($feed, $folder->getId());

$feed = $feedmapper->findWithItems($feed->getId());
echo '<br>' . $feed->getTitle() . '<br>';
$items = $feed->getItems();

foreach($items as $item) {
	$item->setRead();
	if ($item->isRead()) {
		echo $l->t('Read');
	}
	else {
		echo $l->t('Unread');
	}
	
	echo '<br>' . $item->getTitle() . '<br>';
}

$feed2 = $feedmapper->findWithItems(45);
echo '<br>' . $feed2->getTitle() . '<br>';


/*
$item = $feed->get_item(1);


if ($item->isRead()) {
	echo $l->t('Read');
}
else {
	echo $l->t('Unread');
}

$item->setRead();
$item->setUnread();
$item->setRead();

echo "<br>" . $item->get_title() . "<br>";

if ($item->isRead()) {
	echo $l->t('Read');
}
else {
	echo $l->t('Unread');
}
*/