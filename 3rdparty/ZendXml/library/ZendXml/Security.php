<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace ZendXml;

use DOMDocument;
use SimpleXMLElement;

class Security
{
    const ENTITY_DETECT = 'Detected use of ENTITY in XML, disabled to prevent XXE/XEE attacks';

    /**
     * Heuristic scan to detect entity in XML
     *
     * @param  string $xml
     * @throws Exception\RuntimeException
     */
    protected static function heuristicScan($xml)
    {
        if (strpos($xml, '<!ENTITY') !== false) {
            throw new Exception\RuntimeException(self::ENTITY_DETECT);
        }
    }

    /**
     * Scan XML string for potential XXE and XEE attacks
     *
     * @param   string $xml
     * @param   DomDocument $dom
     * @param   Callable(
     *     @param $xml
     *     @param $dom
     *     @return DomDocument|boolean
     * ) $loadCallback if given allows to customize the load command e.g.:
     * function ($xml, $dom) { return $dom->loadHTML($xml, LIBXML_NONET); }
     * @throws  Exception\RuntimeException
     * @return  SimpleXMLElement|DomDocument|boolean
     */
    public static function scan($xml, DOMDocument $dom = null,
                                $loadCallback = null)
    {
        // If running with PHP-FPM we perform an heuristic scan
        // We cannot use libxml_disable_entity_loader because of this bug
        // @see https://bugs.php.net/bug.php?id=64938
        if (self::isPhpFpm()) {
            self::heuristicScan($xml);
        }

        if (null === $dom) {
            $simpleXml = true;
            $dom = new DOMDocument();
        }

        if (!self::isPhpFpm()) {
            $loadEntities = libxml_disable_entity_loader(true);
            $useInternalXmlErrors = libxml_use_internal_errors(true);
        }

        // Load XML with network access disabled (LIBXML_NONET)
        // error disabled with @ for PHP-FPM scenario
        set_error_handler(function ($errno, $errstr) {
            if (substr_count($errstr, 'DOMDocument::loadXML()') > 0) {
                return true;
            }
            return false;
        }, E_WARNING);

        if ($loadCallback) {
            $result = $loadCallback($xml, $dom);
        } else {
            $result = $dom->loadXml($xml, LIBXML_NONET);
        }

        restore_error_handler();

        // Entity load to previous setting
        if (!self::isPhpFpm()) {
            libxml_disable_entity_loader($loadEntities);
            libxml_use_internal_errors($useInternalXmlErrors);
        }

        if (!$result) {
            return false;
        }

        // Scan for potential XEE attacks using ENTITY, if not PHP-FPM
        if (!self::isPhpFpm()) {
            foreach ($dom->childNodes as $child) {
                if ($child->nodeType === XML_DOCUMENT_TYPE_NODE) {
                    if ($child->entities->length > 0) {
                        throw new Exception\RuntimeException(self::ENTITY_DETECT);
                    }
                }
            }
        }

        if (isset($simpleXml)) {
            $result = simplexml_import_dom($dom);
            if (!$result instanceof SimpleXMLElement) {
                return false;
            }
            return $result;
        }
        return $dom;
    }

    /**
     * Scan XML file for potential XXE/XEE attacks
     *
     * @param  string $file
     * @param  DOMDocument $dom
     * @throws Exception\InvalidArgumentException
     * @return SimpleXMLElement|DomDocument
     */
    public static function scanFile($file, DOMDocument $dom = null)
    {
        if (!file_exists($file)) {
            throw new Exception\InvalidArgumentException(
                "The file $file specified doesn't exist"
            );
        }
        return self::scan(file_get_contents($file), $dom);
    }

    /**
     * Return true if PHP is running with PHP-FPM
     *
     * @return boolean
     */
    public static function isPhpFpm()
    {
        if (substr(php_sapi_name(), 0, 3) === 'fpm') {
            return true;
        }
        return false;
    }
}
