<?php

	function print_collection_list($list) {
		
		foreach($list as $collection) {
			if ($collection instanceOf OCA\News\Folder){
				$tmpl_folder = new OCP\Template("news", "part.listfolder");
				$tmpl_folder->assign('folder', $collection);
				$tmpl_folder->printpage();
				print_collection_list($collection->getChildren());
				echo '</ul></li>';
			}
			elseif ($collection instanceOf OCA\News\Feed) { //onhover $(element).attr('id', 'newID');
				$itemmapper = new OCA\News\ItemMapper();

				$items = $itemmapper->findAll($collection->getId());
				$counter = 0;
				foreach($items as $item) {
					if(!$item->isRead())
						++$counter;
				}
				$tmpl_feed = new OCP\Template("news", "part.listfeed");
				$tmpl_feed->assign('feed', $collection);
				$tmpl_feed->assign('unreadItemsCount',$counter);
				$tmpl_feed->printpage();
			}
			else {
			//TODO:handle error in this case
			}
		}
	
	}

	$allfeeds = isset($_['allfeeds']) ? $_['allfeeds'] : '';
	
	print_collection_list($allfeeds);
