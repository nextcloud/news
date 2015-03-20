<?php
/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Alessandro Cosentino <cosenal@gmail.com>
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Alessandro Cosentino 2012
 * @copyright Bernhard Posselt 2012, 2014
 */

namespace OCA\News\Config;

use SimpleXMLElement;

use \OCP\INavigationManager;
use \OCP\IURLGenerator;
use \OCP\Backgroundjob;
use \OCP\Util;
use \OCP\App;

// Used to parse app.json file, should be in core at some point
class AppConfig {

    private $config;
    private $navigationManager;
    private $urlGenerator;

    /**
     * TODO: External deps that are needed:
     * - add jobs
     * - connect to hooks
     */
    public function __construct(INavigationManager $navigationManager,
                                IURLGenerator $urlGenerator) {
        $this->navigationManager = $navigationManager;
        $this->urlGenerator = $urlGenerator;
        $this->config = [];
    }


    /**
     * Parse an xml config
     */
    private function parseConfig($string) {
        // no need to worry about XXE since local file
        $xml = simplexml_load_string($string, 'SimpleXMLElement');
        return json_decode(json_encode((array)$xml), true);
    }


    /**
     * @param string|array $data path to the config file or an array with the
     * config
     */
    public function loadConfig($data) {
        if(is_array($data)) {
            $this->config = $data;
        } else {
            $xml = file_get_contents($data);
            $this->config = $this->parseConfig($xml);
        }
    }


    /**
     * @param string $key if given returns the value of the config at index $key
     * @return array|mixed the config
     */
    public function getConfig($key=null) {
        // FIXME: is this function interface a good idea?
        if($key !== null) {
            return $this->config[$key];
        } else {
            return $this->config;
        }
    }


    /**
     * Registers all config options
     */
    public function registerAll() {
        $this->registerNavigation();
        $this->registerHooks();
        // Fuck it lets just do this quick and dirty until core supports this
        Backgroundjob::addRegularTask($this->config['cron']['job'], 'run');
        App::registerAdmin($this->config['id'], $this->config['admin']);
    }


    /**
     * Parses the navigation and creates a navigation entry if needed
     */
    public function registerNavigation() {
        if (array_key_exists('navigation', $this->config)) {
            $this->navigationManager->add(function () {
                $nav =& $this->config['navigation'];

                $navConfig = [
                    'id' => $this->config['id'],
                    'order' => $nav['order'],
                    'name' => $nav['name']
                ];

                $navConfig['href'] = $this->urlGenerator->linkToRoute(
                    $nav['route']
                );
                $navConfig['icon'] = $this->urlGenerator->imagePath(
                    $this->config['id'], $nav['icon']
                );

                return $navConfig;
            });
        }
    }


    /**
     * Registers all hooks in the config
     */
    public function registerHooks() {
        // FIXME: this is temporarily static because core emitters are not
        // future proof, therefore legacy code in here
        foreach ($this->config['hooks'] as $hook) {
            $listener = explode('::', $hook['channel']);
            $reaction = explode('::', $hook['subscriber']);

            // config is written like HookNamespace::method => Class::method
            Util::connectHook($listener[0], $listener[1], $reaction[0],
                                   $reaction[1]);
        }
    }


}
