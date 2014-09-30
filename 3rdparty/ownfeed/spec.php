<?php
// TODO: atom has type attribute for values that have html/xhtml if given and have to be decoded
// TODO: link attribute (rel etc)
// TODO: can we distinguish between CDATA and plain content?

$ownFeed = new OwnFeed([
	'user_agent' => 'ownFeed version 1',
	'connection_timeout' => 10,
	'timeout' => 10,
	'verify_ssl' => true,
	'http_version' => '1.1',
	'proxy_host' => '',
	'proxy_port' => 80,
	'proxy_user' => '',
	'proxy_password' => ''
]);

try {
	$feed = $ownFeed->fetch($url);
	// RSS || ATOM
	$feed['title'];  // <title>
	$feed['link'];  // <link> || <link href="">

	foreach($feed['items'] as $item) {
		$item['title'];  // <title>
		$item['link'];  // <link>  || <link href="">
		$item['description'];  // <description> || <summary> vs <content>
		foreach ($item['authors'] as $author) {  // <author> or <author><name><email></author>
			$author['name'];
			$author['email'];
		}
		$item['enclosure']['url'];  // <enclosure url type>  || <link rel="enclosure" type href>
		$item['enclosure']['type'];
		$item['id'];  // <guid> || <id> || $item['hash']
		$item['hash'];  // hash over title + content
		$item['pub_date'];  // rfc 822 || rfc 3339
	}
} catch (OwnFeedException $e) {
	// SSLVerificationException
	// TimeoutException
	// ForbiddenException
	// NotFoundException
	// MaximumRedirectException
	// BadSourceException
}

/*
curl_setopt($ch, CURLOPT_URL, $this->url);
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, ini_get('open_basedir') === '');
curl_setopt($ch, CURLOPT_MAXREDIRS, $this->max_redirects);
curl_setopt($ch, CURLOPT_ENCODING, '');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For auto-signed certificates...
curl_setopt($ch, CURLOPT_WRITEFUNCTION, array($this, 'readBody'));
curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, 'readHeaders'));
*/