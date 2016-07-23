<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Alessandro Cosentino <cosenal@gmail.com>
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Alessandro Cosentino 2012
 * @copyright Bernhard Posselt 2012, 2014
 */

namespace OCA\News\Plugin\Client;

/**
 * We actually really want to avoid this global list of plugins. A way would be
 * for News plugin apps to register themselves in a special database table
 * and the News app would just pull out the scripts that should be attached
 * but atm there is no really good way since there is no uninstall hook which
 * would remove the plugin from the apps so fk it :)
 */
class Plugin {

    private static $scripts = [];
    private static $styles = [];

    public static function registerStyle($appName, $styleName) {
        self::$styles[$appName] = $styleName;
    }

    public static function registerScript($appName, $scriptName) {
        self::$scripts[$appName] = $scriptName;
    }

    public static function getStyles() {
        return self::$styles;
    }

    public static function getScripts() {
        return self::$scripts;
    }

}