<?php 

$feed = new SimplePie_Core();
$feed->set_item_class('OC_News_Item');
$feed->set_feed_url( 'http://algorithmsforthekitchen.com/blog/?feed=rss2' );
$feed->enable_cache( false );
$feed->init();
$feed->handle_content_type();

$item = $feed->get_item(1);

if ($item->isRead())
	echo $l->t('Read');
else
	echo $l->t('Unread');

$item->setRead();
$item->setUnread();
$item->setRead();

echo "<br>" . $item->get_title() . "<br>";

if ($item->isRead()) {
	echo $l->t('Read');
}
else
	echo $l->t('Unread');

?>

<?php /* this is from apptemplate

<h1>This is an example app template</h1>

<?php echo $l->t('Some Setting');?>
:"
<?php echo $_['somesetting']; ?>
"
*/ ?>