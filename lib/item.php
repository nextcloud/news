<?php
/**
* ownCloud - News app
*
* @author Alessandro Cosentino
* @copyright 2012 Alessandro Cosentino cosenal@gmail.com
* 
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either 
* version 3 of the License, or any later version.
* 
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*  
* You should have received a copy of the GNU Lesser General Public 
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
* 
*/


class StatusFlag{
	const Unread = 0x02;
	const Important = 0x04;
	const Deleted = 0x08;
	const Updated = 0x16;
}

/*
* This class models an item
*
* It wraps a SimplePie item and adds a status flag to it
*/
class OC_News_Item{

	private $spitem; //the SimplePie item 
	private $status; //a bit-field set with status flags

	public function __construct($spitem){
		$this->spitem = $spitem;
		$this->status |= StatusFlag::Unread; 
	}

	public function setRead(){
		$this->status |= ~StatusFlag::Unread; 
	}

	public function setUnread(){
		$this->status |= StatusFlag::Unread; 
	}

	public function isRead(){
		return ($this->status & ~StatusFlag::Unread);
	}
}
