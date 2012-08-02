<?php

include("populateroot.php");

$output = new OCP\Template("news", "part.feeddialog");
$output->assign('allfeeds', $allfeeds);
$output->printpage();