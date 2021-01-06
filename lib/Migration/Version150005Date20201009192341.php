<?php

declare(strict_types=1);

namespace OCA\News\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Auto-generated migration step: Please modify to your needs!
 */
class Version150005Date20201009192341 extends SimpleMigrationStep {

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
        $qb = $this->connection->getQueryBuilder();

        $qb->update('news_feeds')
            ->set('folder_id', $qb->createPositionalParameter(null, IQueryBuilder::PARAM_NULL))
            ->where('folder_id = 0')
            ->execute();

        $feed_name = $this->connection->getQueryBuilder()->getTableName('news_feeds');
        $folder_name = $this->connection->getQueryBuilder()->getTableName('news_folders');

        $items_query = "DELETE FROM ${feed_name} WHERE ${feed_name}.`folder_id` NOT IN (SELECT DISTINCT id FROM ${folder_name}) AND ${feed_name}.`folder_id` IS NOT NULL";
        $this->connection->executeQuery($items_query);

        $item_name = $this->connection->getQueryBuilder()->getTableName('news_items');
        $feed_name = $this->connection->getQueryBuilder()->getTableName('news_feeds');

        $items_query = "DELETE FROM ${item_name} WHERE ${item_name}.`feed_id` NOT IN (SELECT DISTINCT id FROM ${feed_name})";
        $this->connection->executeQuery($items_query);
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
            !$schema->getTable('news_items')->hasForeignKey('feed')) {

            $schema->getTable('news_items')
                ->addForeignKeyConstraint(
                    $schema->getTable('news_feeds')->getName(),
                    ['feed_id'],
                    ['id'],
                    ['onDelete' => 'CASCADE'],
                    'feed'
                );
        }

        if ($schema->hasTable('news_feeds') &&
            !$schema->getTable('news_feeds')->hasForeignKey('folder')) {

            $schema->getTable('news_feeds')
                ->addForeignKeyConstraint(
                    $schema->getTable('news_folders')->getName(),
                    ['folder_id'],
                    ['id'],
                    ['onDelete' => 'CASCADE'],
                    'folder'
                );
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
	}
}
