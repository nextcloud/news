<?php
/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2014
 */

namespace OCA\News\Controller;

use \OCA\News\Db\IAPI;


class EntityApiSerializer {

    private $level;

    public function __construct($level) {
        $this->level = $level;
    }


    /**
     * Call toAPI() method on all entities. Works on
     *
     * @param mixed $data :
     * * Entity
     * * Entity[]
     * * array('level' => Entity[])
     * * Response
     * @return array|mixed
     */
    public function serialize($data) {

        if($data instanceof IAPI) {
            return [$this->level => [$data->toAPI()]];
        }

        if(is_array($data) && array_key_exists($this->level, $data)) {
            $data[$this->level] = $this->convert($data[$this->level]);
        } elseif(is_array($data)) {
            $data = [$this->level => $this->convert($data)];
        }

        return $data;
    }


    private function convert($entities) {
        $converted = [];

        foreach($entities as $entity) {
            if($entity instanceof IAPI) {
                $converted[] = $entity->toAPI();

            // break if it contains anything else than entities
            } else {
                return $entities;
            }
        }

        return $converted;
    }

}