<?php

declare(strict_types=1);

namespace OCA\News\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

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