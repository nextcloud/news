<?php

namespace PicoFeed\Encoding;

/**
 * Encoding class
 *
 * @package Encoding
 */
class Encoding
{
    public static function convert($input, $encoding)
    {
        if ($encoding === 'utf-8' || $encoding === '') {
            return $input;
        }

        // convert input to utf-8; ignore malformed characters
        return iconv($encoding, 'UTF-8//IGNORE', $input);
    }
}
