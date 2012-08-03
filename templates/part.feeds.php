<?php

	function print_folder(OC_News_Folder $folder){
		$tmpl_folder = new OCP\Template("news", "part.listfolder");
		$tmpl_folder->assign('folder', $folder);
		$tmpl_folder->printpage();

		$children = $folder->getChildren();
		foreach($children as $child) {
			if ($child instanceOf OC_News_Folder){
				print_folder($child);
			}
			elseif ($child instanceOf OC_News_Feed) { //onhover $(element).attr('id', 'newID');
				$itemmapper = new OC_News_ItemMapper();

				$items = $itemmapper->findAll($child->getId());
				$counter = 0;
				foreach($items as $item) {
					if(!$item->isRead())
						++$counter;
				}
				$tmpl_feed = new OCP\Template("news", "part.listfeed");
				$tmpl_feed->assign('child', $child);
				$tmpl_feed->assign('unreadItems',$counter);
				$tmpl_feed->printpage();
			}
			else {
			//TODO:handle error in this case
			}
		}
		echo '</ul></div></li></ul>';
	}

	print_folder($_['allfeeds']);
?>