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
class Version150004Date20201009183830 extends SimpleMigrationStep {

    protected $connection;

    public function __construct(IDBConnection $connection)
    {
        $this->connection = $connection;
    }

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 *
	 * @return void
	 */
	public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options) {
	}

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        if ($schema->hasTable('news_items') &&
            $schema->getTable('news_items')->hasColumn('feed_id')) {
            $schema->getTable('news_items')
                ->getColumn('feed_id')
                ->setNotnull(true)
                ->setUnsigned(true);
        }
        if ($schema->hasTable('news_feeds') &&
            $schema->getTable('news_feeds')->hasColumn('folder_id')) {
            $schema->getTable('news_feeds')
                ->getColumn('folder_id')
                ->setUnsigned(true)
                ->setNotnull(false)
                ->setDefault(null);
        }

        return $schema;
	}

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 *
	 * @return void
	 */
	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options) {
	    $item_name = $this->connection->getQueryBuilder()->getTableName('news_items');
	    $feed_name = $this->connection->getQueryBuilder()->getTableName('news_feeds');

	    $items_query = "DELETE FROM ${item_name} WHERE ${item_name}.`feed_id` NOT IN (SELECT DISTINCT id FROM ${feed_name})";
        $this->connection->executeQuery($items_query);
	}
}
