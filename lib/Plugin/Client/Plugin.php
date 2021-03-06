<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Alessandro Cosentino <cosenal@gmail.com>
 * @author    Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright 2012 Alessandro Cosentino
 * @copyright 2012-2014 Bernhard Posselt
 */

namespace OCA\News\Plugin\Client;

/**
 * We actually really want to avoid this global list of plugins. A way would be
 * for News plugin apps to register themselves in a special database table
 * and the News app would just pull out the scripts that should be attached
 * but atm there is no really good way since there is no uninstall hook which
 * would remove the plugin from the apps so fk it :)
 */
class Plugin
{

    private static $scripts = [];
    private static $styles = [];

    public static function registerStyle($appName, $styleName): void
    {
        self::$styles[$appName] = $styleName;
    }

    public static function registerScript($appName, $scriptName): void
    {
        self::$scripts[$appName] = $scriptName;
    }

    public static function getStyles()
    {
        return self::$styles;
    }

    public static function getScripts()
    {
        return self::$scripts;
    }
}
