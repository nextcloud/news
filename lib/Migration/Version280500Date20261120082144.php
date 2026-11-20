<?php

declare(strict_types=1);

namespace OCA\News\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version280500Date20261120082144 extends SimpleMigrationStep
{
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper
    {
        $schema = $schemaClosure();
        $table = $schema->getTable('news_feeds');

        if (!$table->hasColumn('max_unread_age')) {
            $table->addColumn('max_unread_age', 'integer', [
                'notnull' => true,
                'default' => 0,
                'unsigned' => true,
            ]);
        }

        return $schema;
    }
}
