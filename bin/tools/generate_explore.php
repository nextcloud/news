<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2016
 */

/**
 * This is used for generating a JSON config section for a feed by executing:
 * php -f generate_authors.php www.feed.com
 */

require_once __DIR__ . '/../../vendor/autoload.php';

if (count($argv) < 2 || count($argv) > 3) {
	print('Usage: php -f generate_explore http://path.com/feed [vote_count]');
	print("\n");
	exit();
} elseif (count($argv) === 3) {
	$votes = $argv[2];
} else {
	$votes = 100;
}

$url = $argv[1];

try {
	$config = new PicoFeed\Config\Config();
	$reader = new PicoFeed\Reader\Reader($config);
	$resource = $reader->discover($url);

	$location = $resource->getUrl();
	$content = $resource->getContent();
	$encoding = $resource->getEncoding();

	$parser = $reader->getParser($location, $content, $encoding);

	$feed = $parser->execute();

	$favicon = new PicoFeed\Reader\Favicon($config);

	$result = [
		"title" => $feed->getTitle(),
		"favicon" => $favicon->find($url),
		"url" => $feed->getSiteUrl(),
		"feed" => $feed->getFeedUrl(),
		"description" => $feed->getDescription(),
		"votes" => $votes
	];

	if ($feed->getLogo()) {
		$result["image"] = $feed->getLogo();
	}

	print(json_encode($result, JSON_PRETTY_PRINT));

} catch (\Exception $ex) {
	print($ex->getMessage());
}

print("\n");