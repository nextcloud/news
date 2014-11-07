<?php

namespace PicoFeed\Logging;

use DateTime;
use DateTimeZone;

/**
 * Logging class
 *
 * @author  Frederic Guillot
 * @package Logging
 */
class Logging
{
    /**
     * List of messages
     *
     * @static
     * @access private
     * @var array
     */
    private static $messages = array();

    /**
     * Default timezone
     *
     * @static
     * @access private
     * @var string
     */
    private static $timezone = 'UTC';

    /**
     * Add a new message
     *
     * @static
     * @access public
     * @param  string   $message   Message
     */
    public static function setMessage($message)
    {
        $date = new DateTime('now', new DateTimeZone(self::$timezone));

        self::$messages[] = '['.$date->format('Y-m-d H:i:s').'] '.$message;
    }

    /**
     * Get all logged messages
     *
     * @static
     * @access public
     * @return array
     */
    public static function getMessages()
    {
        return self::$messages;
    }

    /**
     * Remove all logged messages
     *
     * @static
     * @access public
     */
    public static function deleteMessages()
    {
        self::$messages = array();
    }

    /**
     * Set a different timezone
     *
     * @static
     * @see    http://php.net/manual/en/timezones.php
     * @access public
     * @param  string   $timezone   Timezone
     */
    public static function setTimeZone($timezone)
    {
        self::$timezone = $timezone ?: self::$timezone;
    }

    /**
     * Get all messages serialized into a string
     *
     * @static
     * @access public
     * @return string
     */
    public static function toString()
    {
        return implode(PHP_EOL, self::$messages).PHP_EOL;
    }
}
