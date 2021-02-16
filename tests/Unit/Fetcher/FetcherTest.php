<?php

/**
 * Nextcloud - News
 *
 * @author    Alessandro Cosentino
 * @author    Bernhard Posselt
 * @copyright 2012 Alessandro Cosentino cosenal@gmail.com
 * @copyright 2012 Bernhard Posselt dev@bernhard-posselt.com
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\News\Tests\Unit\Fetcher;


use OCA\News\Fetcher\Fetcher;
use OCA\News\Fetcher\IFeedFetcher;
use PHPUnit\Framework\TestCase;

class FetcherTest extends TestCase
{

    private $fetcher;

    protected function setUp(): void
    {
        $this->fetcher = new Fetcher();
    }


    public function testFetch()
    {
        $url = 'hi';
        $mockFetcher = $this->getMockBuilder(IFeedFetcher::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockFetcher->expects($this->once())
            ->method('canHandle')
            ->with($this->equalTo($url))
            ->will($this->returnValue(true));
        $mockFetcher->expects($this->once())
            ->method('fetch')
            ->with(
                $this->equalTo($url),
                $this->equalTo(1),
                $this->equalTo(2),
                $this->equalTo(3)
            );
        $this->fetcher->registerFetcher($mockFetcher);

        $this->fetcher->fetch($url, 1, 2, 3);
    }


    public function testNoFetchers()
    {
        $url = 'hi';
        $mockFetcher = $this->getMockBuilder(IFeedFetcher::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockFetcher->expects($this->once())
            ->method('canHandle')
            ->with($this->equalTo($url))
            ->will($this->returnValue(false));
        $mockFetcher2 = $this->getMockBuilder(IFeedFetcher::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockFetcher2->expects($this->once())
            ->method('canHandle')
            ->with($this->equalTo($url))
            ->will($this->returnValue(false));

        $this->fetcher->registerFetcher($mockFetcher);
        $this->fetcher->registerFetcher($mockFetcher2);

        $result = $this->fetcher->fetch($url);
        $this->assertEquals([null, []], $result);
    }

    public function testMultipleFetchers()
    {
        $url = 'hi';
        $mockFetcher = $this->getMockBuilder(IFeedFetcher::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockFetcher->expects($this->once())
            ->method('canHandle')
            ->with($this->equalTo($url))
            ->will($this->returnValue(false));
        $mockFetcher2 = $this->getMockBuilder(IFeedFetcher::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockFetcher2->expects($this->once())
            ->method('canHandle')
            ->with($this->equalTo($url))
            ->will($this->returnValue(true));

        $this->fetcher->registerFetcher($mockFetcher);
        $this->fetcher->registerFetcher($mockFetcher2);

        $this->fetcher->fetch($url);
    }


    public function testMultipleFetchersOnlyOneShouldHandle()
    {
        $url = 'hi';
        $return = [];
        $mockFetcher = $this->getMockBuilder(IFeedFetcher::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockFetcher->expects($this->once())
            ->method('canHandle')
            ->with($this->equalTo($url))
            ->will($this->returnValue(true));
        $mockFetcher->expects($this->once())
            ->method('fetch')
            ->with($this->equalTo($url))
            ->will($this->returnValue($return));
        $mockFetcher2 = $this->getMockBuilder(IFeedFetcher::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockFetcher2->expects($this->never())
            ->method('canHandle');

        $this->fetcher->registerFetcher($mockFetcher);
        $this->fetcher->registerFetcher($mockFetcher2);

        $result = $this->fetcher->fetch($url);

        $this->assertEquals($return, $result);
    }


}
