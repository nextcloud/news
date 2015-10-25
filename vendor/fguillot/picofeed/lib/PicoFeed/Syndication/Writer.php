<?php

namespace PicoFeed\Syndication;

use RuntimeException;

/**
 * Base writer class.
 *
 * @author    Frederic Guillot
 */
abstract class Writer
{
    /**
     * Dom object.
     *
     * @var \DomDocument
     */
    protected $dom;

    /**
     * Items.
     *
     * @var array
     */
    public $items = array();

    /**
     * Author.
     *
     * @var array
     */
    public $author = array();

    /**
     * Feed URL.
     *
     * @var string
     */
    public $feed_url = '';

    /**
     * Website URL.
     *
     * @var string
     */
    public $site_url = '';

    /**
     * Feed title.
     *
     * @var string
     */
    public $title = '';

    /**
     * Feed description.
     *
     * @var string
     */
    public $description = '';

    /**
     * Feed modification date (timestamp).
     *
     * @var int
     */
    public $updated = 0;

    /**
     * Generate the XML document.
     *
     * @abstract
     *
     * @param string $filename Optional filename
     *
     * @return string
     */
    abstract public function execute($filename = '');

    /**
     * Check required properties to generate the output.
     *
     * @param array $properties List of properties
     * @param mixed $container  Object or array container
     */
    public function checkRequiredProperties(array $properties, $container)
    {
        foreach ($properties as $property) {
            if ((is_object($container) && !isset($container->$property)) || (is_array($container) && !isset($container[$property]))) {
                throw new RuntimeException('Required property missing: '.$property);
            }
        }
    }
}
