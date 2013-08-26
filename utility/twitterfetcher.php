<?php

/**
* ownCloud - News
*
* @author Alessandro Cosentino
* @author Bernhard Posselt
* @copyright 2012 Alessandro Cosentino cosenal@gmail.com
* @copyright 2012 Bernhard Posselt dev@bernhard-posselt.com
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
* You should have received a copy of the GNU Affero General Public
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
*
*/

namespace OCA\News\Utility;


class TwitterFetcher implements IFeedFetcher {


	private $fetcher;
	private $regex;


	public function __construct(FeedFetcher $fetcher){
		$this->fetcher = $fetcher;

		// matches the following urls and extracts the username
		// https://twitter.com/GeorgeTakei
		// https://www.twitter.com/GeorgeTakei
		// http://twitter.com/GeorgeTakei
		// http://www.twitter.com/GeorgeTakei
		// www.twitter.com/GeorgeTakei
		// twitter.com/GeorgeTakei
		$this->regex = '/^(?:https?:\/\/)?(?:www\.)?' .
						'twitter.com\/([\pL\pN\pM]+)$/u';
	}


	public function canHandle($url){
		return preg_match($this->regex, $url) == true;
	}


	public function fetch($url, $getFavicon=true){
		preg_match($this->regex, $url, $match);

		// FIXME: implement twitter api to be future proof
		// deprecated Twitter RSS API
		$rssUrl = 'https://api.twitter.com/1/statuses/user_timeline.' .
					'rss?screen_name=' . $match[1];

		// use transformed url to fetch the rss feeds with the default fetcher
		return $this->fetcher->fetch($rssUrl, $getFavicon);
	}


}