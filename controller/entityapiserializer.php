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

use \OCP\AppFramework\Http\IResponseSerializer;


class EntityApiSeralizer implements IResponseSerializer {


    public function __construct($level) {
        $this->level = $level;
    }


    /**
     * Wrap a list of entities in an array with $level as index and serialize
     * them using the toAPI method
     */
    public function serialize($data) {
        if(!is_array($data)) {
            $data = array($data);
        }

        $response = array(
            $this->level => array();
        );

        foreach($data as $entity) {
            $response[$this->level][] = $entity->toAPI()
        }
    }


}