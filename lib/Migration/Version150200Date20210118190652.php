<?php

declare(strict_types=1);

namespace OCA\News\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Auto-generated migration step: Please modify to your needs!
 */
class Version150200Date20210118190652 extends SimpleMigrationStep {

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
            $schema->getTable('news_items')->hasColumn('pub_date') &&
            $schema->getTable('news_items')->getColumn('pub_date')->getUnsigned()) {
            $schema->getTable('news_items')
                ->getColumn('pub_date')
                ->setUnsigned(false);
        }

        if ($schema->hasTable('news_items') &&
            $schema->getTable('news_items')->hasColumn('updated_date') &&
            $schema->getTable('news_items')->getColumn('updated_date')->getUnsigned()
        ) {
            $schema->getTable('news_items')
                ->getColumn('updated_date')
                ->setUnsigned(false);
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
