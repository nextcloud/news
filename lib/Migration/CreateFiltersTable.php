<?php

declare(strict_types=1);

namespace OCA\News\Migration;

use OCP\Migration\IOutput;
use OCP\Migration\IRepairStep;
use OCP\IDBConnection;
use OCP\Server;

class CreateFiltersTable implements IRepairStep
{
    public function getName(): string
    {
        return 'Create news_filters table and add filtered column to news_items';
    }

    public function run(IOutput $output): void
    {
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

        $columns = $db->executeQuery("SHOW COLUMNS FROM oc_news_items LIKE 'filtered'")->fetchAll();
        if (empty($columns)) {
            $db->executeStatement("
                ALTER TABLE oc_news_items 
                ADD COLUMN filtered BOOLEAN NOT NULL DEFAULT false
            ");
        }

        $output->info('news_filters table and filtered column created successfully');
    }
}