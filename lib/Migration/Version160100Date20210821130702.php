<?php

declare(strict_types=1);

namespace OCA\News\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\IDBConnection;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Auto-generated migration step: Please modify to your needs!
 */
class Version160100Date20210821130702 extends SimpleMigrationStep {

    /**
     * @var IDBConnection
     */
    protected $connection;

    public function __construct(IDBConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param IOutput $output
     * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
     * @param array $options
     */
    public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
    }

    /**
     * @param IOutput $output
     * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
     * @param array $options
     * @return null|ISchemaWrapper
     */
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        if (!$schema->hasTable('news_user_items')) {
            $table = $schema->createTable('news_user_items');
            $table->addColumn('item_id', 'bigint', [
                'notnull' => true,
                'length' => 8,
                'unsigned' => true,
            ]);
            $table->addColumn('user_id', 'string', [
                'notnull' => true,
                'length' => 64,
            ]);
            $table->addColumn('unread', 'boolean', [
                'notnull' => true,
                'default' => false,
            ]);
            $table->addColumn('starred', 'boolean', [
                'notnull' => true,
                'default' => false,
            ]);
            $table->addColumn('last_modified', 'bigint', [
                'notnull' => false,
                'length' => 8,
                'default' => 0,
                'unsigned' => true,
            ]);
            $table->addColumn('shared_by', 'string', [
                'notnull' => false,
                'length' => 64
            ]);
            $table->setPrimaryKey(['item_id', 'user_id']);
        }

        if (!$schema->hasTable('news_user_feeds')) {
            $table = $schema->createTable('news_user_feeds');
            $table->addColumn('feed_id', 'bigint', [
                'notnull' => true,
                'length' => 8,
                'unsigned' => true,
            ]);
            $table->addColumn('user_id', 'string', [
                'notnull' => true,
                'length' => 64,
            ]);
            $table->addColumn('folder_id', 'bigint', [
                'notnull' => false,
                'length' => 8,
            ]);
            $table->addColumn('deleted_at', 'bigint', [
                'notnull' => false,
                'length' => 8,
                'default' => 0,
                'unsigned' => true,
            ]);
            $table->addColumn('added', 'bigint', [
                'notnull' => false,
                'length' => 8,
                'default' => 0,
                'unsigned' => true,
            ]);
            $table->addColumn('title', 'text', [
                'notnull' => true,
            ]);
            $table->addColumn('last_modified', 'bigint', [
                'notnull' => false,
                'length' => 8,
                'default' => 0,
                'unsigned' => true,
            ]);
            $table->setPrimaryKey(['feed_id', 'user_id']);
        }

        return $schema;
    }

    /**
     * @param IOutput $output
     * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
     * @param array $options
     */
    public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
        $qb = $this->connection->getQueryBuilder();
        $user_item_table = $qb->getTableName('news_user_items');
        $user_feed_table = $qb->getTableName('news_user_feeds');
        $item_table = $qb->getTableName('news_items');
        $feed_table = $qb->getTableName('news_feeds');

        $items_query = "REPLACE INTO $user_item_table SELECT id AS 'item_id', ? AS 'user_id',`unread`,`starred`,`last_modified`,`shared_by` FROM $item_table where feed_id = ?;";

        $feeds = $this->connection->executeQuery("SELECT `id`,`user_id` FROM $feed_table;")->fetchAll();
        foreach ($feeds as $feed) {
            $this->connection->executeUpdate($items_query, [$feed['user_id'], $feed['id']]);
        }

        $this->connection->executeUpdate("REPLACE INTO $user_feed_table SELECT id AS 'feed_id',user_id,folder_id,deleted_at,added,title,last_modified FROM $feed_table;");
    }
}
