<?php 

$att = 0;
$prova = 0x2;
$att &= ~0x2;
print($att);


$feedmapper = new OC_News_FeedMapper();
$foldermapper = new OC_News_FolderMapper();

$folder = new OC_News_Folder( 'Friends' );
$folderid = $foldermapper->save($folder);

$feed = OC_News_Utils::fetch( 'http://algorithmsforthekitchen.com/blog/?feed=rss2' );
echo '<br>' . $feed->getTitle() . '<br>';

$feedmapper->save($feed, $folder->getId());

$feed = $feedmapper->findWithItems($feed->getId());
echo '<br>' . $feed->getTitle() . '<br>';
$items = $feed->getItems();

foreach($items as $item) {

	echo $item->getTitle() . ' - ';
	if ($item->isRead()) {
		echo $l->t('Read');
	}
	else {
		echo $l->t('Unread');
	}
	echo '<br>';
	$item->setRead();
}

foreach($items as $item) {
	echo $item->getStatus();
}

echo '<br>';

$feedmapper->save($feed, $folder->getId());

echo '<br>...after saving and reloading';

$feed = $feedmapper->findWithItems($feed->getId());
echo '<br>' . $feed->getTitle() . '<br>';
$items = $feed->getItems();

foreach($items as &$item) {

	echo $item->getTitle() . ' - ';
	if ($item->isRead()) {
		echo $l->t('Read');
	}
	else {
		echo $l->t('Unread');
	}
	echo '<br>';
}
