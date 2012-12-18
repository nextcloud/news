<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>News - ownCloud</title>
  </head>
  <body>
  	<div class="message">
	<?php

	// Check if we are a user
	OCP\User::checkLoggedIn();
	OCP\App::checkAppEnabled('news');
	$userid = OCP\USER::getUser();

	$feedurl = isset($_GET['url']) ? $_GET['url'] : null;
	$feedmapper = new OCA\News\FeedMapper($userid);
	$feedid = $feedmapper->findIdFromUrl($feedurl);

	$l = OC_L10N::get('news');

	if ($feedid === null) {
		$feed = OCA\News\Utils::slimFetch($feedurl);

		if ($feed !== null) {
		      $feedid = $feedmapper->save($feed, 0); //adds in the root folder
		}

		if($feed === null || !$feedid) {
			echo $l->t('An error occurred');
		} else {
			echo $l->t('Nice! You have subscribed to ') . $feed->getTitle();
		}
	}
	else {
		echo $l->t('You had already subscribed to this feed!');
	}

	?>
	</div>
	<a href="javascript:self.close()" >Close this window</a>
  </body>
</html>
