<?php
/**
* ownCloud - News app
*
* @author Alessandro Cosentino
* Copyright (c) 2012 - Alessandro Cosentino <cosenal@gmail.com>
*
* This file is licensed under the Affero General Public License version 3 or later.
* See the COPYING-README file
*
*/

namespace OCA\News;


class Enclosure {
    private $mimetype;
    private $link;

    public function getMimeType() {
        return $this->mimetype;
    }
    
    public function setMimeType($mimetype) {
        $this->mimetype = $mimetype;
    }

    public function getLink() {
        return $this->link;
    }
    
    public function setLink($link) {
        $this->link = $link;
    }
}
