<?php

namespace PicoFeed\Client;

use PicoFeed\Logging\Logging;

/**
 * cURL HTTP client
 *
 * @author  Frederic Guillot
 * @package Client
 */
class Curl extends Client
{
    /**
     * HTTP response body
     *
     * @access private
     * @var string
     */
    private $body = '';

    /**
     * Body size
     *
     * @access private
     * @var integer
     */
    private $body_length = 0;

    /**
     * HTTP response headers
     *
     * @access private
     * @var array
     */
    private $headers = array();

    /**
     * Counter on the number of header received
     *
     * @access private
     * @var integer
     */
    private $headers_counter = 0;

    /**
     * cURL callback to read the HTTP body
     *
     * If the function return -1, curl stop to read the HTTP response
     *
     * @access public
     * @param  resource  $ch       cURL handler
     * @param  string    $buffer   Chunk of data
     * @return integer   Length of the buffer
     */
    public function readBody($ch, $buffer)
    {
        $length = strlen($buffer);
        $this->body_length += $length;

        if ($this->body_length > $this->max_body_size) {
            return -1;
        }

        $this->body .= $buffer;

        return $length;
    }

    /**
     * cURL callback to read HTTP headers
     *
     * @access public
     * @param  resource  $ch       cURL handler
     * @param  string    $buffer   Header line
     * @return integer   Length of the buffer
     */
    public function readHeaders($ch, $buffer)
    {
        $length = strlen($buffer);

        if ($buffer === "\r\n") {
            $this->headers_counter++;
        }
        else {

            if (! isset($this->headers[$this->headers_counter])) {
                $this->headers[$this->headers_counter] = '';
            }

            $this->headers[$this->headers_counter] .= $buffer;
        }

        return $length;
    }

    /**
     * Prepare HTTP headers
     *
     * @access private
     * @return array
     */
    private function prepareHeaders()
    {
        $headers = array(
            'Connection: close',
            'User-Agent: '.$this->user_agent,
        );

        if ($this->etag) {
            $headers[] = 'If-None-Match: '.$this->etag;
        }

        if ($this->last_modified) {
            $headers[] = 'If-Modified-Since: '.$this->last_modified;
        }

        return $headers;
    }

    /**
     * Prepare curl proxy context
     *
     * @access private
     * @return resource
     */
    private function prepareProxyContext($ch)
    {
        if ($this->proxy_hostname) {

            Logging::setMessage(get_called_class().' Proxy: '.$this->proxy_hostname.':'.$this->proxy_port);

            curl_setopt($ch, CURLOPT_PROXYPORT, $this->proxy_port);
            curl_setopt($ch, CURLOPT_PROXYTYPE, 'HTTP');
            curl_setopt($ch, CURLOPT_PROXY, $this->proxy_hostname);

            if ($this->proxy_username) {
                Logging::setMessage(get_called_class().' Proxy credentials: Yes');
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->proxy_username.':'.$this->proxy_password);
            }
            else {
                Logging::setMessage(get_called_class().' Proxy credentials: No');
            }
        }

        return $ch;
    }

    /**
     * Prepare curl context
     *
     * @access private
     * @return resource
     */
    private function prepareContext()
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->prepareHeaders());
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, ini_get('open_basedir') === '');
        curl_setopt($ch, CURLOPT_MAXREDIRS, $this->max_redirects);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, array($this, 'readBody'));
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, 'readHeaders'));
        curl_setopt($ch, CURLOPT_COOKIEJAR, 'php://memory');
        curl_setopt($ch, CURLOPT_COOKIEFILE, 'php://memory');

        $ch = $this->prepareProxyContext($ch);

        return $ch;
    }

    /**
     * Execute curl context
     *
     * @access private
     */
    private function executeContext()
    {
        $ch = $this->prepareContext();
        curl_exec($ch);

        Logging::setMessage(get_called_class().' cURL total time: '.curl_getinfo($ch, CURLINFO_TOTAL_TIME));
        Logging::setMessage(get_called_class().' cURL dns lookup time: '.curl_getinfo($ch, CURLINFO_NAMELOOKUP_TIME));
        Logging::setMessage(get_called_class().' cURL connect time: '.curl_getinfo($ch, CURLINFO_CONNECT_TIME));
        Logging::setMessage(get_called_class().' cURL speed download: '.curl_getinfo($ch, CURLINFO_SPEED_DOWNLOAD));
        Logging::setMessage(get_called_class().' cURL effective url: '.curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));

        $curl_errno = curl_errno($ch);

        if ($curl_errno) {
            Logging::setMessage(get_called_class().' cURL error: '.curl_error($ch));
            curl_close($ch);

            $this->handleError($curl_errno);
        }

        curl_close($ch);
    }

    /**
     * Do the HTTP request
     *
     * @access public
     * @param  bool    $follow_location    Flag used when there is an open_basedir restriction
     * @return array                       HTTP response ['body' => ..., 'status' => ..., 'headers' => ...]
     */
    public function doRequest($follow_location = true)
    {
        $this->executeContext();

        list($status, $headers) = $this->parseHeaders(explode("\r\n", $this->headers[$this->headers_counter - 1]));

        // When resticted with open_basedir
        if ($this->needToHandleRedirection($follow_location, $status)) {
            return $this->handleRedirection($headers['Location']);
        }

        return array(
            'status' => $status,
            'body' => $this->body,
            'headers' => $headers
        );
    }

    /**
     * Check if the redirection have to be handled manually
     *
     * @access private
     * @param  boolean    $follow_location    Flag
     * @param  integer    $status             HTTP status code
     * @return boolean
     */
    private function needToHandleRedirection($follow_location, $status)
    {
        return $follow_location && ini_get('open_basedir') !== '' && ($status == 301 || $status == 302);
    }

    /**
     * Handle manually redirections when there is an open base dir restriction
     *
     * @access private
     * @param  string     $location       Redirected URL
     * @return boolean|array
     */
    private function handleRedirection($location)
    {
        $nb_redirects = 0;
        $this->url = $location;
        $this->body = '';
        $this->body_length = 0;
        $this->headers = array();
        $this->headers_counter = 0;

        while (true) {

            $nb_redirects++;

            if ($nb_redirects >= $this->max_redirects) {
                return false;
            }

            $result = $this->doRequest(false);

            if ($result['status'] == 301 || $result['status'] == 302) {
                $this->url = $result['headers']['Location'];
                $this->body = '';
                $this->body_length = 0;
                $this->headers = array();
                $this->headers_counter = 0;
            }
            else {
                return $result;
            }
        }

        return false;
    }

    /**
     * Handle cURL errors (throw individual exceptions)
     *
     * We don't use constants because they are not necessary always available
     * (depends of the version of libcurl linked to php)
     *
     * @see    http://curl.haxx.se/libcurl/c/libcurl-errors.html
     * @access private
     * @param  integer     $errno    cURL error code
     */
    private function handleError($errno)
    {
        switch ($errno) {
            case 78: // CURLE_REMOTE_FILE_NOT_FOUND
                throw new InvalidUrlException('Resource not found');
            case 6:  // CURLE_COULDNT_RESOLVE_HOST
                throw new InvalidUrlException('Unable to resolve hostname');
            case 7:  // CURLE_COULDNT_CONNECT
                throw new InvalidUrlException('Unable to connect to the remote host');
            case 28: // CURLE_OPERATION_TIMEDOUT
                throw new TimeoutException('Operation timeout');
            case 35: // CURLE_SSL_CONNECT_ERROR
            case 51: // CURLE_PEER_FAILED_VERIFICATION
            case 58: // CURLE_SSL_CERTPROBLEM
            case 60: // CURLE_SSL_CACERT
            case 59: // CURLE_SSL_CIPHER
            case 64: // CURLE_USE_SSL_FAILED
            case 66: // CURLE_SSL_ENGINE_INITFAILED
            case 77: // CURLE_SSL_CACERT_BADFILE
            case 83: // CURLE_SSL_ISSUER_ERROR
                throw new InvalidCertificateException('Invalid SSL certificate');
            case 47: // CURLE_TOO_MANY_REDIRECTS
                throw new MaxRedirectException('Maximum number of redirections reached');
            case 63: // CURLE_FILESIZE_EXCEEDED
                throw new MaxSizeException('Maximum response size exceeded');
            default:
                throw new InvalidUrlException('Unable to fetch the URL');
        }
    }
}
