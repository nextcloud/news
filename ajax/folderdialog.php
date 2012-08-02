<?php

include("populateroot.php");

$output = new OCP\Template("news", "part.folderdialog");
$output->assign('allfeeds', $allfeeds);
$output->printpage();