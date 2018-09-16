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

namespace OCA\News\Tests\Unit\Controller;

use OCA\News\Controller\EntityApiSerializer;
use \OCP\AppFramework\Http\Response;
use \OCP\AppFramework\Db\Entity;

use \OCA\News\Db\Item;

class TestEntity extends Entity
{

}


class EntityApiSerializerTest extends \PHPUnit_Framework_TestCase
{


    public function testSerializeSingle() 
    {
        $item = new Item();
        $item->setUnread(true);
        $item->setId(3);
        $item->setGuid('guid');
        $item->setGuidHash('guidhash');
        $item->setFeedId(123);

        $serializer = new EntityApiSerializer('items');
        $result = $serializer->serialize($item);

        $this->assertTrue($result['items'][0]['unread']);
    }


    public function testSerializeMultiple() 
    {
        $item = new Item();
        $item->setUnread(true);
        $item->setId(3);
        $item->setGuid('guid');
        $item->setGuidHash('guidhash');
        $item->setFeedId(123);

        $item2 = new Item();
        $item2->setUnread(false);
        $item2->setId(5);
        $item2->setGuid('guid');
        $item2->setGuidHash('guidhash');
        $item2->setFeedId(123);

        $serializer = new EntityApiSerializer('items');

        $result = $serializer->serialize([$item, $item2]);

        $this->assertTrue($result['items'][0]['unread']);
        $this->assertFalse($result['items'][1]['unread']);
    }


    public function testResponseNoChange() 
    {
        $response = new Response();
        $serializer = new EntityApiSerializer('items');

        $result = $serializer->serialize($response);

        $this->assertEquals($response, $result);
    }


    public function testCompleteArraysTransformed() 
    {
        $item = new Item();
        $item->setUnread(true);
        $item->setId(3);
        $item->setGuid('guid');
        $item->setGuidHash('guidhash');
        $item->setFeedId(123);

        $item2 = new Item();
        $item2->setUnread(false);
        $item2->setId(5);
        $item2->setGuid('guid');
        $item2->setGuidHash('guidhash');
        $item2->setFeedId(123);

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


    public function testNoEntityNoChange() 
    {
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


    public function testEntitiesNoChange() 
    {
        $serializer = new EntityApiSerializer('items');

        $in = [
            'items' => [new TestEntity()]
        ];

        $result = $serializer->serialize($in);

        $this->assertEquals($in, $result);
    }
}