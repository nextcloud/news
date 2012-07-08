<?php

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('news');

$foldermapper = new OC_News_FolderMapper(OCP\USER::getUser());

$allfeeds = $foldermapper->populate('All Feeds', 0);

if ($allfeeds) {
	$feedid = isset( $_GET['feedid'] ) ? $_GET['feedid'] : null;
	if ($feedid == null) {

	}
}
else {
	$feedid = 0;
}

$output = new OCP\Template("news", "part.addfeedfolder");
$output -> assign('allfeeds', $allfeeds);
$output -> printpage();