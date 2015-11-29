<?php
/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2015
 */


namespace OCA\News\Tests\Integration\Fixtures;


trait Entity {

    public function fillDefaults(array $defaults=[]) {
        foreach ($defaults as $key => $value) {
            $method = 'set' . ucfirst($key);
            $this->$method($value);
        }
        $this->resetUpdatedFields();
    }

}