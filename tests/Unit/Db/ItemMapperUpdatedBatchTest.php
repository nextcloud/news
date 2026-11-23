<?php
/**
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 */
namespace OCA\News\Tests\Unit\Db;

use OCA\News\Db\Item;
use OCA\News\Db\ItemMapperV2;
use OCA\News\Db\ListType;
use OCA\News\Utility\Time;
use OCP\DB\QueryBuilder\IQueryBuilder;

class ItemMapperUpdatedBatchTest extends MapperTestUtility
{
    /** @dataProvider filters */
    public function testBatchFilters(int $type, ?int $beforeId, array $extraConditions, array $parameters): void
    {
        $conditions = [];
        $actualParameters = [];
        $joins = [];
        $this->db->method('getQueryBuilder')->willReturn($this->builder);
        $this->builder->method('select')->with('items.*')->willReturnSelf();
        $this->builder->method('from')->with('news_items', 'items')->willReturnSelf();
        $this->builder->method('innerJoin')->willReturnCallback(function (...$args) use (&$joins) {
            $joins[] = $args;
            return $this->builder;
        });
        $this->builder->method('andWhere')->willReturnCallback(function ($condition) use (&$conditions) {
            $conditions[] = $condition;
            return $this->builder;
        });
        $this->builder->method('setParameters')->willReturnCallback(function ($params) use (&$actualParameters) {
            $actualParameters = $params;
            return $this->builder;
        });
        $this->builder->method('setParameter')->willReturnCallback(function ($name, $value) use (&$actualParameters) {
            $actualParameters[$name] = $value;
            return $this->builder;
        });
        $this->builder->expects($this->once())->method('addOrderBy')->with('items.id', 'DESC')->willReturnSelf();
        $this->builder->expects($this->once())->method('setMaxResults')->with(100)->willReturnSelf();
        $this->builder->method('executeQuery')->willReturn($this->cursor);
        $this->cursor->method('fetchAssociative')->willReturnOnConsecutiveCalls(['id' => 7], false);
        $mapper = new ItemMapperV2($this->db, $this->createMock(Time::class));
        $items = $mapper->findUpdatedBatch('alice', $type, 42, 123456, $beforeId, 100);
        $this->assertEquals([Item::fromRow(['id' => 7])], $items);
        $expectedConditions = [
            'items.last_modified >= :updatedSince', 'feeds.deleted_at = 0', 'feeds.user_id = :userId',
        ];
        if ($beforeId !== null) {
            $expectedConditions[] = 'items.id < :beforeId';
            $parameters = ['beforeId' => $beforeId] + $parameters;
        }
        $this->assertSame(array_merge($expectedConditions, $extraConditions), $conditions);
        $this->assertSame(['updatedSince' => 123456, 'userId' => 'alice'] + $parameters, $actualParameters);
        $expectedJoins = [['items', 'news_feeds', 'feeds', 'items.feed_id = feeds.id']];
        if ($type === ListType::FOLDER) {
            $expectedJoins[] = ['feeds', 'news_folders', 'folders', 'feeds.folder_id = folders.id'];
        }
        $this->assertSame($expectedJoins, $joins);
    }

    public static function filters(): array
    {
        return [
            'all first page' => [ListType::ALL_ITEMS, null, [], []],
            'all subsequent page' => [ListType::ALL_ITEMS, 50, [], []],
            'feed' => [ListType::FEED, 50, ['feeds.id = :feedId'], ['feedId' => 42]],
            'folder' => [ListType::FOLDER, 50, ['folders.id = :folderId'], ['folderId' => 42]],
            'starred' => [ListType::STARRED, 50, ['items.starred = :starred'], ['starred' => true]],
            'unread' => [ListType::UNREAD, 50, ['items.unread = :unread'], ['unread' => true]],
        ];
    }

    /** @dataProvider invalidBatchSizes */
    public function testInvalidBatchSize(int $size): void
    {
        $this->db->expects($this->never())->method('getQueryBuilder');
        $this->expectException(\InvalidArgumentException::class);
        (new ItemMapperV2($this->db, $this->createMock(Time::class)))
            ->findUpdatedBatch('alice', ListType::ALL_ITEMS, 0, 0, null, $size);
    }

    public static function invalidBatchSizes(): array
    {
        return [[0], [-1], [501]];
    }
}
