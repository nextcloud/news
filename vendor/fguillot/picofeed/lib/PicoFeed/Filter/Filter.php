<?php

namespace PicoFeed\Filter;

use PicoFeed\Parser\XmlParser;

/**
 * Filter class
 *
 * @author  Frederic Guillot
 * @package Filter
 */
class Filter
{
    /**
     * Get the Html filter instance
     *
     * @static
     * @access public
     * @param  string  $html      HTML content
     * @param  string  $website   Site URL (used to build absolute URL)
     * @return PicoFeed\Filter\Html
     */
    public static function html($html, $website)
    {
        $filter = new Html($html, $website);
        return $filter;
    }

    /**
     * Escape HTML content
     *
     * @static
     * @access public
     * @return string
     */
    public static function escape($content)
    {
        return @htmlspecialchars($content, ENT_QUOTES, 'UTF-8', false);
    }

    /**
     * Remove HTML tags
     *
     * @access public
     * @param  string  $data  Input data
     * @return string
     */
    public function removeHTMLTags($data)
    {
        return preg_replace('~<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>\s*~i', '', $data);
    }

    /**
     * Remove the XML tag from a document
     *
     * @static
     * @access public
     * @param  string  $data  Input data
     * @return string
     */
    public static function stripXmlTag($data)
    {
        if (strpos($data, '<?xml') !== false) {
            $data = ltrim(substr($data, strpos($data, '?>') + 2));
        }

        do {

            $pos = strpos($data, '<?xml-stylesheet ');

            if ($pos !== false) {
                $data = ltrim(substr($data, strpos($data, '?>') + 2));
            }

        } while ($pos !== false && $pos < 200);

        return $data;
    }

    /**
     * Strip head tag from the HTML content
     *
     * @static
     * @access public
     * @param  string  $data  Input data
     * @return string
     */
    public static function stripHeadTags($data)
    {
        $start = strpos($data, '<head>');
        $end = strpos($data, '</head>');

        if ($start !== false && $end !== false) {
            $before = substr($data, 0, $start);
            $after = substr($data, $end + 7);
            $data = $before.$after;
        }

        return $data;
    }

    /**
     * Trim whitespace from the begining, the end and inside a string and don't break utf-8 string
     *
     * @static
     * @access public
     * @param  string  $value  Raw data
     * @return string          Normalized data
     */
    public static function stripWhiteSpace($value)
    {
        $value = str_replace("\r", ' ', $value);
        $value = str_replace("\t", ' ', $value);
        $value = str_replace("\n", ' ', $value);
        // $value = preg_replace('/\s+/', ' ', $value); <= break utf-8
        return trim($value);
    }

    /**
     * Dirty quickfixes before XML parsing
     *
     * @static
     * @access public
     * @param  string  $data Raw data
     * @return string        Normalized data
     */
    public static function normalizeData($data)
    {
        $invalid_chars = array(
            "\x10",
            "\xc3\x20",
            "&#x1F;",
        );

        foreach ($invalid_chars as $needle) {
            $data = str_replace($needle, '', $data);
        }

        return $data;
    }
}
