<?php foreach( $_['allfeeds'] as $collection ):
	$children = $collection->getChildren();
	echo sizeof($children);
	foreach($children as $child) {
		if ($child instanceOf OC_News_Folder){
			echo 'prova';
			echo $child->getName(); 
		}
	}
echo 'prova';	
?>
<?php endforeach; ?>