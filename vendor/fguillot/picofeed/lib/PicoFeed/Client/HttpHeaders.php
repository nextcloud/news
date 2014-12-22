<?php

namespace PicoFeed\Client;

use ArrayAccess;

/**
 * Class to handle http headers case insensitivity
 *
 * @author  Bernhard Posselt
 * @package Client
 */
class HttpHeaders implements ArrayAccess
{
    private $headers = array();

    public function __construct(array $headers)
    {
        foreach ($headers as $key => $value) {
            $this->headers[strtolower($key)] = $value;
        }
    }

    public function offsetGet($offset)
    {
        return $this->headers[strtolower($offset)];
    }

    public function offsetSet($offset, $value)
    {
        $this->headers[strtolower($offset)] = $value;
    }

    public function offsetExists($offset)
    {
        return isset($this->headers[strtolower($offset)]);
    }

    public function offsetUnset($offset)
    {
        unset($this->headers[strtolower($offset)]);
    }
}
