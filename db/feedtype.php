<?php
/**
* ownCloud - News app
*
* @author Bernhard Posselt
* Copyright (c) 2012 - Bernhard Posselt <nukeawhale@gmail.com>
*
* This file is licensed under the Affero General Public License version 3 or later.
* See the COPYING-README file
*
*/

namespace OCA\News;


class FeedType {
    const FEED          = 0;
    const FOLDER        = 1;
    const STARRED       = 2;
    const SUBSCRIPTIONS = 3;
    const SHARED	= 4;
};