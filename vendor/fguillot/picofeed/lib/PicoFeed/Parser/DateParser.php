<?php

namespace PicoFeed\Parser;

use DateTime;
use DateTimeZone;

/**
 * Date Parser
 *
 * @author  Frederic Guillot
 * @package Parser
 */
class DateParser
{
    /**
     * Timezone used to parse feed dates
     *
     * @access public
     * @var string
     */
    public $timezone = 'UTC';

    /** 
     * Supported formats [ 'format' => length ]
     *
     * @access public
     * @var array
     */
    public $formats = array(
        DATE_ATOM => null,
        DATE_RSS => null,
        DATE_COOKIE => null,
        DATE_ISO8601 => null,
        DATE_RFC822 => null,
        DATE_RFC850 => null,
        DATE_RFC1036 => null,
        DATE_RFC1123 => null,
        DATE_RFC2822 => null,
        DATE_RFC3339 => null,
        'D, d M Y H:i:s' => 25,
        'D, d M Y h:i:s' => 25,
        'D M d Y H:i:s' => 24,
        'j M Y H:i:s' => 20,
        'Y-m-d H:i:s' => 19,
        'Y-m-d\TH:i:s' => 19,
        'd/m/Y H:i:s' => 19,
        'D, d M Y' => 16,
        'Y-m-d' => 10,
        'd-m-Y' => 10,
        'm-d-Y' => 10,
        'd.m.Y' => 10,
        'm.d.Y' => 10,
        'd/m/Y' => 10,
        'm/d/Y' => 10,
    );

    /**
     * Try to parse all date format for broken feeds
     *
     * @access public
     * @param  string  $value  Original date format
     * @return integer         Timestamp
     */
    public function getTimestamp($value)
    {
        $value = trim($value);

        foreach ($this->formats as $format => $length) {

            $truncated_value = $value;
            if ($length !== null) {
                $truncated_value = substr($truncated_value, 0, $length);
            }

            $timestamp = $this->getValidDate($format, $truncated_value);
            if ($timestamp > 0) {
                return $timestamp;
            }
        }

        $date = new DateTime('now', new DateTimeZone($this->timezone));
        return $date->getTimestamp();
    }

    /**
     * Get a valid date from a given format
     *
     * @access public
     * @param  string  $format   Date format
     * @param  string  $value    Original date value
     * @return integer           Timestamp
     */
    public function getValidDate($format, $value)
    {
        $date = DateTime::createFromFormat($format, $value, new DateTimeZone($this->timezone));

        if ($date !== false) {

            $errors = DateTime::getLastErrors();

            if ($errors['error_count'] === 0 && $errors['warning_count'] === 0) {
                return $date->getTimestamp();
            }
        }

        return 0;
    }
}
