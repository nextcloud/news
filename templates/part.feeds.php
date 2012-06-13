<?php 
	echo $_['allfeeds']->getName();
	$children = $_['allfeeds']->getChildren();

	foreach($children as $child) {
		if ($child instanceOf OC_News_Folder){
			echo $child->getName(); 
		}
	}
?>
