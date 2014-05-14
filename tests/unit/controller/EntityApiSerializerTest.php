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

namespace OCA\News\Controller;

require_once(__DIR__ . "/../../classloader.php");


use \OCP\AppFramework\Http\Response;

use \OCA\News\Db\Item;


class EntityApiSerializerTest extends \PHPUnit_Framework_TestCase {


    public function testSerializeSingle() {
        $item = new Item();
        $item->setUnread();

        $serializer = new EntityApiSerializer('items');
        $result = $serializer->serialize($item);

        $this->assertTrue($result['items'][0]['unread']);
    }


    public function testSerializeMultiple() {
        $item = new Item();
        $item->setUnread();

        $item2 = new Item();
        $item2->setRead();

        $serializer = new EntityApiSerializer('items');

        $result = $serializer->serialize([$item, $item2]);

        $this->assertTrue($result['items'][0]['unread']);
        $this->assertFalse($result['items'][1]['unread']);
    }


    public function testResponseNoChange() {
        $response = new Response();
        $serializer = new EntityApiSerializer('items');

        $result = $serializer->serialize($response);

        $this->assertEquals($response, $result);
    }


    public function testCompleteArraysTransformed() {
        $item = new Item();
        $item->setUnread();

        $item2 = new Item();
        $item2->setRead();

        $serializer = new EntityApiSerializer('items');

        $in = [
            'items' => [$item, $item2],
            'test' => 1
        ];

        $result = $serializer->serialize($in);

        $this->assertTrue($result['items'][0]['unread']);
        $this->assertFalse($result['items'][1]['unread']);
        $this->assertEquals(1, $result['test']);
    }


    public function noEntityNoChange() {
        $serializer = new EntityApiSerializer('items');

        $in = [
            'items' => ['hi', '2'],
            'test' => 1
        ];

        $result = $serializer->serialize($in);

        $this->assertEquals('hi', $result['items'][0]);
        $this->assertEquals('2', $result['items'][1]);
        $this->assertEquals(1, $result['test']);
    }

}