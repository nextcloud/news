<?php

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('news');

$foldermapper = new OC_News_FolderMapper(OCP\USER::getUser());
$l = new OC_l10n('news');

$folder = new OC_News_Folder($l->t('Everything'), 0);

$allfeeds = $foldermapper->populate($folder);

if ($allfeeds) {
	$feedid = isset( $_GET['feedid'] ) ? $_GET['feedid'] : null;
	if ($feedid == null) {

	}
}
else {
	$feedid = 0;
}
