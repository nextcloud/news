<?php
	function print_folder(OC_News_Folder $folder, $depth){
		echo '<li style="margin-left:' . 10*$depth . 'px;" class="menuItem" onclick="News.DropDownMenu.selectItem(this, ' . $folder->getId() . ')">' . strtoupper($folder->getName()) . '</li>';
		$children = $folder->getChildren();
		foreach($children as $child) {
			if ($child instanceOf OC_News_Folder){
				print_folder($child, $depth+1);
			}
		}
	}
	print_folder($_['allfeeds'], 0);
?>