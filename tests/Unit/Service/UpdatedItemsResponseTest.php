<?php
/**
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 */
namespace OCA\News\Tests\Unit\Service;

use OCA\News\Db\Item;
use OCA\News\Db\ItemMapperV2;
use OCA\News\Db\ListType;
use OCA\News\Service\Exceptions\ServiceValidationException;
use OCA\News\Service\UpdatedItemsResponse;
use OCP\AppFramework\Http\IOutput;
use OCP\AppFramework\Http\StreamResponse;
use OCP\IDBConnection;
use PHPUnit\Framework\TestCase;

class UpdatedItemsResponseTest extends TestCase
{
    private $db;
    private $mapper;
    private $events;

    protected function setUp(): void
    {
        $this->db = $this->createMock(IDBConnection::class);
        $this->mapper = $this->createMock(ItemMapperV2::class);
        $this->events = [];
        $this->db->method('inTransaction')->willReturn(false);
        $this->db->method('executeStatement')->willReturnCallback(function ($sql) {
            $this->assertSame('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ, READ ONLY', $sql);
            $this->events[] = 'isolation';
            return 0;
        });
        $this->db->method('beginTransaction')->willReturnCallback(function () {
            $this->events[] = 'begin';
        });
        $this->db->method('commit')->willReturnCallback(function () {
            $this->events[] = 'commit';
        });
    }

    private function item(int $id): Item
    {
        $item = new Item();
        $item->setId($id);
        $item->setGuid("guid-$id");
        $item->setGuidHash("hash-$id");
        $item->setFeedId(1);
        $item->setBody('<p>Body & unicode: \u{00e9}</p>');
        return $item;
    }

    private function body(StreamResponse $response): string
    {
        $body = '';
        $output = $this->createMock(IOutput::class);
        $output->method('getHttpResponseCode')->willReturn(200);
        $output->expects($this->once())->method('setReadfile')->willReturnCallback(
            function ($stream) use (&$body) {
                $this->assertIsResource($stream);
                $stat = fstat($stream);
                $this->assertSame(0600, $stat['mode'] & 0777);
                $body = stream_get_contents($stream);
                return strlen($body);
            }
        );
        $response->callback($output);
        $this->assertSame((string)strlen($body), $response->getHeaders()['Content-Length']);
        $this->assertSame('private, no-store', $response->getHeaders()['Cache-Control']);
        return $body;
    }

    /** @dataProvider providers */
    public function testCompleteResponseAcrossPages(string $provider, array $events): void
    {
        $this->db->method('getDatabaseProvider')->willReturn($provider);
        $items = array_map(fn ($id) => $this->item($id), range(201, 1));
        $pages = 0;
        $this->mapper->expects($this->exactly(3))->method('findUpdatedBatch')->willReturnCallback(
            function ($user, $type, $id, $since, $before, $limit) use ($items, &$pages) {
                $this->assertSame(['alice', 3, 0, 123, 100], [$user, $type, $id, $since, $limit]);
                $this->assertSame([null, 102, 2][$pages], $before);
                $this->events[] = 'page';
                return array_slice($items, 100 * $pages++, 100);
            }
        );
        $response = (new UpdatedItemsResponse($this->mapper, $this->db))->createResponse('alice', 3, 0, 123);
        $expected = json_encode(['items' => array_map(fn ($item) => $item->toAPI(), $items)], JSON_HEX_TAG, 2048);
        $this->assertSame($expected, $this->body($response));
        $this->assertSame($events, $this->events);
    }

    public static function providers(): array
    {
        return [
            'postgresql' => ['postgres', ['begin', 'isolation', 'page', 'page', 'page', 'commit']],
            'mysql' => ['mysql', ['isolation', 'begin', 'page', 'page', 'page', 'commit']],
            'mariadb' => ['mariadb', ['isolation', 'begin', 'page', 'page', 'page', 'commit']],
            'sqlite' => ['sqlite', ['begin', 'page', 'page', 'page', 'commit']],
        ];
    }

    public function testEmptyResponse(): void
    {
        $this->db->method('getDatabaseProvider')->willReturn('postgres');
        $this->mapper->expects($this->once())->method('findUpdatedBatch')->willReturn([]);
        $response = (new UpdatedItemsResponse($this->mapper, $this->db))->createResponse('alice', 3, 0, 123);
        $this->assertSame('{"items":[]}', $this->body($response));
    }

    /** @dataProvider failures */
    public function testFailureRollsBackAndClosesSpool(string $failure): void
    {
        $this->db->method('getDatabaseProvider')->willReturn('postgres');
        $this->db->expects($this->never())->method('commit');
        $this->db->expects($this->once())->method('rollBack');
        $stream = $failure === 'write' ? fopen('php://memory', 'r') : tmpfile();
        $service = $this->getMockBuilder(UpdatedItemsResponse::class)
            ->setConstructorArgs([$this->mapper, $this->db])->onlyMethods(['createSpool'])->getMock();
        $service->method('createSpool')->willReturn($stream);
        if ($failure === 'query') {
            $this->mapper->method('findUpdatedBatch')->willThrowException(new \RuntimeException('query failure'));
        } elseif ($failure === 'encoding') {
            $item = $this->item(1);
            $item->setBody("\xB1\x31");
            $this->mapper->method('findUpdatedBatch')->willReturn([$item]);
        } elseif ($failure === 'cursor') {
            $this->mapper->method('findUpdatedBatch')->willReturn([$this->item(1), $this->item(1)]);
        }
        try {
            // A read-only stream deliberately produces a PHP fwrite notice.
            @$service->createResponse('alice', 3, 0, 123);
            $this->fail('A partial response must never be returned');
        } catch (\RuntimeException | \JsonException $e) {
            $this->assertNotEmpty($e->getMessage());
        }
        $this->assertFalse(is_resource($stream));
    }

    public static function failures(): array
    {
        return [['write'], ['query'], ['encoding'], ['cursor']];
    }

    public function testSpoolCreationFailureDoesNotStartTransaction(): void
    {
        $this->db->method('getDatabaseProvider')->willReturn('postgres');
        $this->db->expects($this->never())->method('beginTransaction');
        $service = $this->getMockBuilder(UpdatedItemsResponse::class)
            ->setConstructorArgs([$this->mapper, $this->db])->onlyMethods(['createSpool'])->getMock();
        $service->method('createSpool')->willReturn(false);
        $this->expectException(\RuntimeException::class);
        $service->createResponse('alice', 3, 0, 123);
    }

    public function testCommitFailureClosesSpoolAndPreservesOriginalException(): void
    {
        $db = $this->createMock(IDBConnection::class);
        $db->method('getDatabaseProvider')->willReturn('sqlite');
        $db->method('inTransaction')->willReturn(false);
        $failure = new \RuntimeException('commit failure');
        $db->method('commit')->willThrowException($failure);
        $db->expects($this->once())->method('rollBack')->willThrowException(new \RuntimeException('rollback failure'));
        $this->mapper->method('findUpdatedBatch')->willReturn([]);
        $stream = tmpfile();
        $service = $this->getMockBuilder(UpdatedItemsResponse::class)
            ->setConstructorArgs([$this->mapper, $db])->onlyMethods(['createSpool'])->getMock();
        $service->method('createSpool')->willReturn($stream);
        try {
            $service->createResponse('alice', 3, 0, 123);
            $this->fail('A failed commit must not return a response');
        } catch (\RuntimeException $e) {
            $this->assertSame($failure, $e);
        }
        $this->assertFalse(is_resource($stream));
    }

    public function testIsolationFailureRollsBack(): void
    {
        $db = $this->createMock(IDBConnection::class);
        $db->method('getDatabaseProvider')->willReturn('postgres');
        $db->method('inTransaction')->willReturn(false);
        $db->method('executeStatement')->willThrowException(new \RuntimeException('isolation failure'));
        $db->expects($this->once())->method('rollBack');
        $db->expects($this->never())->method('commit');
        $this->mapper->expects($this->never())->method('findUpdatedBatch');
        $this->expectException(\RuntimeException::class);
        (new UpdatedItemsResponse($this->mapper, $db))->createResponse('alice', 3, 0, 123);
    }

    public function testInvalidTypeDoesNotStartTransaction(): void
    {
        $this->db->expects($this->never())->method('beginTransaction');
        $this->expectException(ServiceValidationException::class);
        (new UpdatedItemsResponse($this->mapper, $this->db))->createResponse('alice', -1, 0, 123);
    }

    public function testExistingTransactionIsNotModified(): void
    {
        $db = $this->createMock(IDBConnection::class);
        $db->method('getDatabaseProvider')->willReturn('postgres');
        $db->method('inTransaction')->willReturn(true);
        $db->expects($this->never())->method('beginTransaction');
        $db->expects($this->never())->method('rollBack');
        $this->expectException(\RuntimeException::class);
        (new UpdatedItemsResponse($this->mapper, $db))->createResponse('alice', ListType::ALL_ITEMS, 0, 123);
    }
}
