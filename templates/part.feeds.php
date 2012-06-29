<?php 
	$allfeeds = $_['allfeeds']->getChildren();

	foreach($allfeeds as $collection) {
		if ($collection instanceOf OC_News_Folder){
			echo $collection->getName() . '<br>'; 
		}
	}
?>
