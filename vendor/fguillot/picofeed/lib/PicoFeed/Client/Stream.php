<?php

namespace PicoFeed\Client;

use PicoFeed\Logging\Logging;

/**
 * Stream context HTTP client
 *
 * @author  Frederic Guillot
 * @package Client
 */
class Stream extends Client
{
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

        if (function_exists('gzdecode')) {
            $headers[] = 'Accept-Encoding: gzip';
        }

        if ($this->etag) {
            $headers[] = 'If-None-Match: '.$this->etag;
        }

        if ($this->last_modified) {
            $headers[] = 'If-Modified-Since: '.$this->last_modified;
        }

        if ($this->proxy_username) {
            $headers[] = 'Proxy-Authorization: Basic '.base64_encode($this->proxy_username.':'.$this->proxy_password);
        }

        return $headers;
    }

    /**
     * Prepare stream context
     *
     * @access private
     * @return array
     */
    private function prepareContext()
    {
        $context = array(
            'http' => array(
                'method' => 'GET',
                'protocol_version' => 1.1,
                'timeout' => $this->timeout,
                'max_redirects' => $this->max_redirects,
            )
        );

        if ($this->proxy_hostname) {

            Logging::setMessage(get_called_class().' Proxy: '.$this->proxy_hostname.':'.$this->proxy_port);

            $context['http']['proxy'] = 'tcp://'.$this->proxy_hostname.':'.$this->proxy_port;
            $context['http']['request_fulluri'] = true;

            if ($this->proxy_username) {
                Logging::setMessage(get_called_class().' Proxy credentials: Yes');
            }
            else {
                Logging::setMessage(get_called_class().' Proxy credentials: No');
            }
        }

        $context['http']['header'] = implode("\r\n", $this->prepareHeaders());

        return $context;
    }

    /**
     * Do the HTTP request
     *
     * @access public
     * @return array   HTTP response ['body' => ..., 'status' => ..., 'headers' => ...]
     */
    public function doRequest()
    {
        // Create context
        $context = stream_context_create($this->prepareContext());

        // Make HTTP request
        $stream = @fopen($this->url, 'r', false, $context);
        if (! is_resource($stream)) {
            throw new InvalidUrlException('Unable to establish a connection');
        }

        // Get the entire body until the max size
        $body = stream_get_contents($stream, $this->max_body_size + 1);

        // If the body size is too large abort everything
        if (strlen($body) > $this->max_body_size) {
            throw new MaxSizeException('Content size too large');
        }

        // Get HTTP headers response
        $metadata = stream_get_meta_data($stream);

        if ($metadata['timed_out']) {
            throw new TimeoutException('Operation timeout');
        }

        list($status, $headers) = $this->parseHeaders($metadata['wrapper_data']);

        fclose($stream);

        return array(
            'status' => $status,
            'body' => $this->decodeBody($body, $headers),
            'headers' => $headers
        );
    }

    /**
     * Decode body response according to the HTTP headers
     *
     * @access public
     * @param  string    $body      Raw body
     * @param  array     $headers   HTTP headers
     * @return string
     */
    public function decodeBody($body, array $headers)
    {
        if (isset($headers['Transfer-Encoding']) && $headers['Transfer-Encoding'] === 'chunked') {
            $body = $this->decodeChunked($body);
        }

        if (isset($headers['Content-Encoding']) && $headers['Content-Encoding'] === 'gzip') {
            $body = @gzdecode($body);
        }

        return $body;
    }

    /**
     * Decode a chunked body
     *
     * @access public
     * @param  string $str Raw body
     * @return string      Decoded body
     */
    public function decodeChunked($str)
    {
        for ($result = ''; ! empty($str); $str = trim($str)) {

            // Get the chunk length
            $pos = strpos($str, "\r\n");
            $len = hexdec(substr($str, 0, $pos));

            // Append the chunk to the result
            $result .= substr($str, $pos + 2, $len);
            $str = substr($str, $pos + 2 + $len);
        }

        return $result;
    }
}
