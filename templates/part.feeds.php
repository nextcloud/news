<?php

	function print_folder(OCA\News\Folder $folder){
		$tmpl_folder = new OCP\Template("news", "part.listfolder");
		$tmpl_folder->assign('folder', $folder);
		$tmpl_folder->printpage();

		$children = $folder->getChildren();
		foreach($children as $child) {
			if ($child instanceOf OCA\News\Folder){
				print_folder($child);
			}
			elseif ($child instanceOf OCA\News\Feed) { //onhover $(element).attr('id', 'newID');
				$itemmapper = new OCA\News\ItemMapper();

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
		echo '</ul></li>';
	}

	$allfeeds = isset($_['allfeeds']) ? $_['allfeeds'] : '';
?>

<?php
print_folder($allfeeds);
?>