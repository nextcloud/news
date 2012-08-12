<?php

	function print_folder_list($folderlist, $depth) {
		foreach($folderlist as $folder) {
			echo '<li style="margin-left:' . 10*$depth . 'px;" class="menuItem" onclick="News.DropDownMenu.selectItem(this, ' . $folder->getId() . ')">' . $folder->getName() . '</li>';
			$children = $folder->getChildren();
			print_folder_list($children, $depth+1);
		}
	}
	
	
	print_folder_list($_['folderforest'], 0);
?>