<?php
	
	function print_folder(OC_News_Folder $folder, $depth){
		echo '<ul style="margin-left:' . 10*$depth . 'px;"> <li style="background-image:url(' . 
			OC_Helper::imagePath('core', 'filetypes/folder.png') . '); background-repeat:no-repeat; background-position:0px 8px; padding-left: 20px; ">' . 
			'<span class="collapsable">' . $folder->getName() .'</span><ul>';
		$children = $folder->getChildren();
		foreach($children as $child) {
			if ($child instanceOf OC_News_Folder){
				print_folder($child, $depth+1);
			}
			elseif ($child instanceOf OC_News_Feed) {
				echo '<li><a href="' . OCP\Util::linkTo('news', 'index.php'). '?feedid=' . $child->getId() . '">' . $child->getTitle() .'</a></li>';
			}
			else {
			//TODO:handle error in this case
			}
		}
		echo '</ul></li></ul>';
	}
	
	print_folder($_['allfeeds'], 0);
?>
