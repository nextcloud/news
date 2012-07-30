<?php 

$content = file_get_contents('/var/www/apps/news/prova.opml');

require_once('news/opmlparser.php');

$parser = new OPMLParser($content);
$title = $parser->getTitle();
$data = $parser->parse();

foreach ($data as $collection) {
	if ($collection instanceof OC_News_Feed) {
		echo $collection->getTitle() . '\n';
	} else {
		echo 'NO\n';
	}
}
echo $title;
