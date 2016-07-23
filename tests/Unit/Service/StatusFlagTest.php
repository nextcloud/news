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

namespace OCA\News\Db;


class StatusFlagTest extends \PHPUnit_Framework_TestCase {

    private $statusFlag;

    protected function setUp(){
        $this->statusFlag = new StatusFlag();
    }


    public function testTypeToStatusUnreadStarred(){
        $expected = StatusFlag::STARRED;
        $status = $this->statusFlag->typeToStatus(FeedType::STARRED, false);

        $this->assertEquals($expected, $status);
    }


    public function testTypeToStatusUnread(){
        $expected = StatusFlag::UNREAD;
        $status = $this->statusFlag->typeToStatus(FeedType::FEED, false);

        $this->assertEquals($expected, $status);
    }


    public function testTypeToStatusReadStarred(){
        $expected = StatusFlag::STARRED;
        $status = $this->statusFlag->typeToStatus(FeedType::STARRED, true);

        $this->assertEquals($expected, $status);
    }


    public function testTypeToStatusRead(){
        $expected = (~StatusFlag::UNREAD) & 0;
        $status = $this->statusFlag->typeToStatus(FeedType::FEED, true);

        $this->assertEquals($expected, $status);
    }

}