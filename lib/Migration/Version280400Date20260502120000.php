<?php

declare(strict_types=1);

namespace OCA\News\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\IDBConnection;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;
use OCP\Server;

class Version280400Date20260502120000 extends SimpleMigrationStep
{
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper
    {
        $schema = $schemaClosure();

        // create news_filters table
        if (!$schema->hasTable('news_filters')) {
            $table = $schema->createTable('news_filters');
            $table->addColumn('id', 'bigint', [
                'autoincrement' => true,
                'notnull'       => true,
                'length'        => 8,
                'unsigned'      => true,
            ]);
            $table->addColumn('feed_id', 'bigint', [
                'notnull'  => true,
                'length'   => 8,
                'unsigned' => true,
            ]);
            $table->addColumn('title_keywords', 'text', [
                'notnull' => false,
                'default' => null,
            ]);
            $table->addColumn('body_keywords', 'text', [
                'notnull' => false,
                'default' => null,
            ]);
            $table->addColumn('url_keywords', 'text', [
                'notnull' => false,
                'default' => null,
            ]);
            $table->addColumn('last_modified', 'bigint', [
                'notnull'  => false,
                'length'   => 8,
                'default'  => 0,
                'unsigned' => true,
            ]);
            $table->setPrimaryKey(['id']);
            $table->addUniqueIndex(['feed_id'], 'news_filters_feed_id_idx');

            // createTable via changeSchema return value may not be applied
            // execute raw SQL directly
            /** @var IDBConnection $db */
            $db = Server::get(IDBConnection::class);
            $db->executeStatement("
                CREATE TABLE IF NOT EXISTS news_filters (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    feed_id BIGINT UNSIGNED NOT NULL,
                    title_keywords TEXT DEFAULT NULL,
                    body_keywords TEXT DEFAULT NULL,
                    url_keywords TEXT DEFAULT NULL,
                    last_modified BIGINT UNSIGNED DEFAULT 0,
                    UNIQUE INDEX news_filters_feed_id_idx (feed_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin
            ");
        }

        // add filtered column to news_items
        $itemsTable = $schema->getTable('news_items');
        if (!$itemsTable->hasColumn('filtered')) {
            $itemsTable->addColumn('filtered', 'boolean', [
                'notnull' => true,
                'default' => false,
            ]);
        }

        return $schema;
    }
}