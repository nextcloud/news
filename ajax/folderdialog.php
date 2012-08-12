<?php

$foldermapper = new OCA\News\FolderMapper(OCP\USER::getUser());
$folderforest = $foldermapper->childrenOf(0); //retrieve all the folders

$output = new OCP\Template("news", "part.folderdialog");
$output->assign('folderforest', $folderforest);
$output->printpage();