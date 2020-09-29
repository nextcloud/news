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

namespace OCA\News\Db;

trait EntityJSONSerializer
{

    /**
     * Serialize object properties.
     *
     * @param array $properties Serializable properties
     *
     * @return array
     */
    public function serializeFields(array $properties): array
    {
        $result = [];
        foreach ($properties as $property) {
            $result[$property] = $this->$property;
        }
        return $result;
    }
}
