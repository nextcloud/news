<?php

namespace PicoFeed\Parser;

use DomDocument;
use DOMXPath;
use SimpleXmlElement;

/**
 * XML parser class
 *
 * Checks for XML eXternal Entity (XXE) and XML Entity Expansion (XEE) attacks on XML documents
 *
 * @author  Frederic Guillot
 * @package Parser
 */
class XmlParser
{
    /**
     * Get a SimpleXmlElement instance or return false
     *
     * @static
     * @access public
     * @param  string   $input   XML content
     * @return mixed
     */
    public static function getSimpleXml($input)
    {
        $dom = self::getDomDocument($input);

        if ($dom !== false) {

            $simplexml = simplexml_import_dom($dom);

            if (! $simplexml instanceof SimpleXmlElement) {
                return false;
            }

            return $simplexml;
        }

        return false;
    }

    /**
     * Get a DomDocument instance or return false
     *
     * @static
     * @access public
     * @param  string   $input   XML content
     * @return mixed
     */
    public static function getDomDocument($input)
    {
        if (substr(php_sapi_name(), 0, 3) === 'fpm') {

            // If running with PHP-FPM and an entity is detected we refuse to parse the feed
            // @see https://bugs.php.net/bug.php?id=64938
            if (strpos($input, '<!ENTITY') !== false) {
                return false;
            }
        }
        else {

            libxml_disable_entity_loader(true);
        }

        libxml_use_internal_errors(true);

        $dom = new DomDocument;
        $dom->loadXml($input, LIBXML_NONET);

        // The document is empty, there is probably some parsing errors
        if ($dom->childNodes->length === 0) {
            return false;
        }

        // Scan for potential XEE attacks using ENTITY
        foreach ($dom->childNodes as $child) {
            if ($child->nodeType === XML_DOCUMENT_TYPE_NODE) {
                if ($child->entities->length > 0) {
                    return false;
                }
            }
        }

        return $dom;
    }

    /**
     * Load HTML document by using a DomDocument instance or return false on failure
     *
     * @static
     * @access public
     * @param  string   $input   XML content
     * @return mixed
     */
    public static function getHtmlDocument($input)
    {
        libxml_use_internal_errors(true);

        $dom = new DomDocument;

        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            $dom->loadHTML($input, LIBXML_NONET);
        }
        else {
            $dom->loadHTML($input);
        }

        return $dom;
    }

    /**
     * Convert a HTML document to XML
     *
     * @static
     * @access public
     * @param  string   $html   HTML document
     * @return string
     */
    public static function HtmlToXml($html)
    {
        $dom = self::getHtmlDocument('<?xml version="1.0" encoding="UTF-8">'.$html);
        return $dom->saveXML($dom->getElementsByTagName('body')->item(0));
    }

    /**
     * Get XML parser errors
     *
     * @static
     * @access public
     * @return string
     */
    public static function getErrors()
    {
        $errors = array();

        foreach(libxml_get_errors() as $error) {

            $errors[] = sprintf('XML error: %s (Line: %d - Column: %d - Code: %d)',
                $error->message,
                $error->line,
                $error->column,
                $error->code
            );
        }

        return implode(', ', $errors);
    }

    /**
     * Get the encoding from a xml tag
     *
     * @static
     * @access public
     * @param  string  $data  Input data
     * @return string
     */
    public static function getEncodingFromXmlTag($data)
    {
        $encoding = '';

        if (strpos($data, '<?xml') !== false) {

            $data = substr($data, 0, strrpos($data, '?>'));
            $data = str_replace("'", '"', $data);

            $p1 = strpos($data, 'encoding=');
            $p2 = strpos($data, '"', $p1 + 10);

            $encoding = substr($data, $p1 + 10, $p2 - $p1 - 10);
            $encoding = strtolower($encoding);
        }

        return $encoding;
    }

    /**
     * Get xml:lang value
     *
     * @static
     * @access public
     * @param  string  $xml  XML string
     * @return string        Language
     */
    public static function getXmlLang($xml)
    {
        $dom = self::getDomDocument($xml);

        if ($dom === false) {
            return '';
        }

        $xpath = new DOMXPath($dom);
        return $xpath->evaluate('string(//@xml:lang[1])') ?: '';
    }

    /**
     * Get a value from a XML namespace
     *
     * @static
     * @access public
     * @param  SimpleXMLElement     $xml           XML element
     * @param  array                $namespaces    XML namespaces
     * @param  string               $property      XML tag name
     * @param  string               $attribute     XML attribute name
     * @return string
     */
    public static function getNamespaceValue(SimpleXMLElement $xml, array $namespaces, $property, $attribute = '')
    {
        foreach ($namespaces as $name => $url) {
            $namespace = $xml->children($namespaces[$name]);

            if ($namespace->$property->count() > 0) {

                if ($attribute) {

                    foreach ($namespace->$property->attributes() as $xml_attribute => $xml_value) {
                        if ($xml_attribute === $attribute && $xml_value) {
                            return (string) $xml_value;
                        }
                    }
                }

                return (string) $namespace->$property;
            }
        }

        return '';
    }
}
