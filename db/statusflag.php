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

class StatusFlag{
	const UNREAD    = 0x02;
	const IMPORTANT = 0x04;
	const DELETED   = 0x08;
	const UPDATED   = 0x16;
}