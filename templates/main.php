<?php 

$mapper = new OC_News_FeedMapper();
$feed = $mapper->fetch( 'http://algorithmsforthekitchen.com/blog/?feed=rss2' );
echo "<br>" . $feed->getTitle() . "<br>";
$mapper->insert($feed);
$feed = $mapper->findWithItems($feed->getId());
echo "<br>" . $feed->getTitle() . "<br>";
$items = $feed->getItems();

foreach($items as $item) {
	echo $item->getTitle();
}

$feed2 = $mapper->findWithItems(45);
echo "<br>" . $feed2->getTitle() . "<br>";


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