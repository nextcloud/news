<?php 

$feed = new OC_News_Feed( 'http://algorithmsforthekitchen.com/blog/?feed=rss2' );
$mapper = new OC_News_FeedMapper();
$mapper->insert($feed);
$mapper->find($feed->getId());
echo "<br>" . $feed->getTitle() . "<br>";

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