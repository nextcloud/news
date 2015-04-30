<?php

namespace PicoFeed\Scraper;

use PicoFeed\Logging\Logger;
use PicoFeed\Config\Config;

/**
 * RuleLoader class
 *
 * @author  Frederic Guillot
 * @author  Bernhard Posselt
 * @package Scraper
 */
class RuleLoader
{
    /**
     * Config object
     *
     * @access private
     * @var \PicoFeed\Config\Config
     */
    private $config;

    /**
     * Constructor
     *
     * @access public
     * @param  \PicoFeed\Config\Config   $config   Config class instance
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Get the rules for an URL
     *
     * @access public
     * @param string $url the URL that should be looked up
     * @return array the array containing the rules
     */
    public function getRules($url)
    {
        $hostname = parse_url($url, PHP_URL_HOST);

        if ($hostname !== false) {

            $files = $this->getRulesFileList($hostname);

            foreach ($this->getRulesFolders() as $folder) {
                $rule = $this->loadRuleFile($folder, $files);

                if (! empty($rule)) {
                    return $rule;
                }
            }
        }

        return array();
    }

    /**
     * Get the list of possible rules file names for a given hostname
     *
     * @access public
     * @param  string  $hostname  Hostname
     * @return array
     */
    public function getRulesFileList($hostname)
    {
        $files = array($hostname);                 // subdomain.domain.tld
        $parts = explode('.', $hostname);
        $len = count($parts);

        if ($len > 2) {
            $subdomain = array_shift($parts);
            $files[] = implode('.', $parts);       // domain.tld
            $files[] = '.'.implode('.', $parts);   // .domain.tld
            $files[] = $subdomain;                 // subdomain
        }
        else if ($len === 2) {
            $files[] = '.'.implode('.', $parts);    // .domain.tld
            $files[] = $parts[0];                   // domain
        }

        return $files;
    }

    /**
     * Load a rule file from the defined folder
     *
     * @access public
     * @param  string   $folder     Rule directory
     * @param  array    $files      List of possible file names
     * @return array
     */
    public function loadRuleFile($folder, array $files)
    {
        foreach ($files as $file) {
            $filename = $folder.'/'.$file.'.php';
            if (file_exists($filename)) {
                Logger::setMessage(get_called_class().' Load rule: '.$file);
                return include $filename;
            }
        }

        return array();
    }

    /**
     * Get the list of folders that contains rules
     *
     * @access public
     * @return array
     */
    public function getRulesFolders()
    {
        $folders = array(__DIR__.'/../Rules');

        if ($this->config !== null && $this->config->getGrabberRulesFolder() !== null) {
            $folders[] = $this->config->getGrabberRulesFolder();
        }

        return $folders;
    }
}
