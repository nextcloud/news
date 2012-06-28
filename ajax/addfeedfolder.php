<?php
 
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('news');

$output = new OCP\Template("news", "part.addfeedfolder");
$output -> printpage();