<?php

$foldermapper = new OCA\News\FolderMapper(OCP\USER::getUser());
$folderforest = $foldermapper->childrenOf(0); //retrieve all the folders

$output = new OCP\Template("news", "part.feeddialog");
$output->assign('folderforest', $folderforest);
$output->printpage();