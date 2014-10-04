<?php
/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Alessandro Cosentino <cosenal@gmail.com>
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Alessandro Cosentino 2012
 * @copyright Bernhard Posselt 2012, 2014
 */

namespace OCA\News\Feed\Client;

class CURL extends HttpClient {

	public function __construct ($version, array $config=null) {
		parent::__construct($version, $config);
	}


	public function get ($url) {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $this->url);
		curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->timeout);
		curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($curl, CURLOPT_USERAGENT, $this->user_agent);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, ini_get('open_basedir') === '');
		curl_setopt($curl, CURLOPT_MAXREDIRS, $this->max_redirects);
		curl_setopt($curl, CURLOPT_ENCODING, '');
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // For auto-signed certificates...
		curl_setopt($curl, CURLOPT_WRITEFUNCTION, array($this, 'readBody'));
		curl_setopt($curl, CURLOPT_HEADERFUNCTION, array($this, 'readHeaders'));

		curl_setopt($curl, CURLOPT_PROXYPORT, $this->proxy_port);
		curl_setopt($curl, CURLOPT_PROXYTYPE, 'HTTP');
		curl_setopt($curl, CURLOPT_PROXY, $this->proxy_hostname);
		curl_setopt($curl, CURLOPT_PROXYUSERPWD, $this->proxy_username.':'.$this->proxy_password);

		curl_errno($curl);
		curl_error($curl);

		curl_exec($curl);
	}
}


