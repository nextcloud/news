<?php
/**
* ownCloud - News app
*
* @author Alessandro Cosentino
* Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
*
* This file is licensed under the Affero General Public License version 3 or later.
* See the COPYING-README file
*
*/

namespace OCA\News;

class Share_Backend_News_Item implements \OCP\Share_Backend {

	const FORMAT_ITEM = 0;
	
	private static $item;

	public function isValidSource($itemSource, $uidOwner) {
		$itemMapper = new ItemMapper($uidOwner);
		$this->item = $itemMapper->findById($itemSource);
		if ($this->item !== null) {
			return true;
		}
		return false;
	}

	public function generateTarget($itemSource, $shareWith, $exclude = null) {
		return $this->item->getTitle();
	}

	public function formatItems($items, $format, $parameters = null) {
		$formattedItems = array();
		foreach ($items as $item) {
			$itemMapper = new ItemMapper($item['uid_owner']);
			$formattedItem = $itemMapper->findById($item['item_source']);
			$formattedItems[] = $formattedItem;
		}
		return $formattedItems;
	}

}