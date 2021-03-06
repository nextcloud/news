<?php

declare(strict_types=1);

namespace OCA\News\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;
use function PHPUnit\Framework\returnValue;

/**
 * Auto-generated migration step: Please modify to your needs!
 */
class Version150203Date20210204203051 extends SimpleMigrationStep {

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

        if ($schema->hasTable('news_items') &&
            $schema->getTable('news_items')->hasColumn('last_modified') &&
            $schema->getTable('news_items')->getColumn('last_modified')->getUnsigned()
        ) {
            $schema->getTable('news_items')
                ->getColumn('last_modified')
                ->setUnsigned(false);
        }

        if ($schema->hasTable('news_items') &&
            $schema->getTable('news_items')->hasColumn('updated_date')
        ) {
            $schema->getTable('news_items')
                ->dropColumn('updated_date');
        }

        if ($schema->hasTable('news_items') &&
            $schema->getTable('news_items')->hasColumn('status')
        ) {
            $schema->getTable('news_items')
                ->dropColumn('status');
        }

        if ($schema->hasTable('news_feeds') &&
            $schema->getTable('news_feeds')->hasColumn('http_etag')
        ) {
            $schema->getTable('news_feeds')
                ->dropColumn('http_etag');
        }

        return $schema;
	}

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 */
	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
	}
}
