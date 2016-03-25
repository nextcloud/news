<?php

namespace PicoFeed\Parser;

use DomDocument;
use SimpleXmlElement;
use Exception;

use ZendXml\Security;

/**
 * XML parser class.
 *
 * Checks for XML eXternal Entity (XXE) and XML Entity Expansion (XEE) attacks on XML documents
 *
 * @author  Frederic Guillot
 */
class XmlParser
{
    /**
     * Get a SimpleXmlElement instance or return false.
     *
     * @static
     *
     * @param string $input XML content
     *
     * @return mixed
     */
    public static function getSimpleXml($input)
    {
        return self::scan($input);
    }

    /**
     * Get a DomDocument instance or return false.
     *
     * @static
     *
     * @param string $input XML content
     *
     * @return \DOMDocument
     */
    public static function getDomDocument($input)
    {
        if (empty($input)) {
            return false;
        }

        $dom = self::scan($input, new DOMDocument());

        // The document is empty, there is probably some parsing errors
        if ($dom && $dom->childNodes->length === 0) {
            return false;
        }

        return $dom;
    }

    /**
     * Small wrapper around ZendXml to turn their exceptions into picoFeed
     * exceptions
     * @param $input the xml to load
     * @param $dom   pass in a dom document or use null/omit if simpleXml should
     * be used
     */
    private static function scan($input, $dom = null)
    {
        try {
            return Security::scan($input, $dom);
        } catch(\ZendXml\Exception\RuntimeException $e) {
            throw new XmlEntityException($e->getMessage());
        }
    }

    /**
     * Load HTML document by using a DomDocument instance or return false on failure.
     *
     * @static
     *
     * @param string $input XML content
     *
     * @return \DOMDocument
     */
    public static function getHtmlDocument($input)
    {
        $dom = new DomDocument();

        if (empty($input)) {
            return $dom;
        }

        libxml_use_internal_errors(true);

        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            $dom->loadHTML($input, LIBXML_NONET);
        } else {
            $dom->loadHTML($input);
        }

        return $dom;
    }

    /**
     * Convert a HTML document to XML.
     *
     * @static
     *
     * @param string $html HTML document
     *
     * @return string
     */
    public static function htmlToXml($html)
    {
        $dom = self::getHtmlDocument('<?xml version="1.0" encoding="UTF-8">'.$html);

        return $dom->saveXML($dom->getElementsByTagName('body')->item(0));
    }

    /**
     * Get XML parser errors.
     *
     * @static
     *
     * @return string
     */
    public static function getErrors()
    {
        $errors = array();

        foreach (libxml_get_errors() as $error) {
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
     * Get the encoding from a xml tag.
     *
     * @static
     *
     * @param string $data Input data
     *
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

            if ($p1 !== false && $p2 !== false) {
                $encoding = substr($data, $p1 + 10, $p2 - $p1 - 10);
                $encoding = strtolower($encoding);
            }
        }

        return $encoding;
    }

    /**
     * Get the charset from a meta tag.
     *
     * @static
     *
     * @param string $data Input data
     *
     * @return string
     */
    public static function getEncodingFromMetaTag($data)
    {
        $encoding = '';

        if (preg_match('/<meta.*?charset\s*=\s*["\']?\s*([^"\'\s\/>;]+)/i', $data, $match) === 1) {
            $encoding = strtolower($match[1]);
        }

        return $encoding;
    }

    /**
     * Rewrite XPath query to use namespace-uri and local-name derived from prefix.
     *
     * @param string $query XPath query
     * @param array  $ns    Prefix to namespace URI mapping
     *
     * @return string
     */
    public static function replaceXPathPrefixWithNamespaceURI($query, array $ns)
    {
        return preg_replace_callback('/([A-Z0-9]+):([A-Z0-9]+)/iu', function ($matches) use ($ns) {
            // don't try to map the special prefix XML
            if (strtolower($matches[1]) === 'xml') {
                return $matches[0];
            }

            return '*[namespace-uri()="'.$ns[$matches[1]].'" and local-name()="'.$matches[2].'"]';
        },
        $query);
    }

    /**
     * Get the result elements of a XPath query.
     *
     * @param \SimpleXMLElement $xml   XML element
     * @param string            $query XPath query
     * @param array             $ns    Prefix to namespace URI mapping
     *
     * @return \SimpleXMLElement
     */
    public static function getXPathResult(SimpleXMLElement $xml, $query, array $ns = array())
    {
        if (!empty($ns)) {
            $query = static::replaceXPathPrefixWithNamespaceURI($query, $ns);
        }

        return $xml->xpath($query);
    }
}
