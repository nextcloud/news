<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2024 Benjamin Brahmer <info@b-brahmer.de>
 *
 * @author Benjamin Brahmer <info@b-brahmer.de>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\News\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * FIXME Auto-generated migration step: Please modify to your needs!
 */
class Version250000Date20240817095614 extends SimpleMigrationStep {

    /**
     * @param IOutput $output
     * @param Closure(): ISchemaWrapper $schemaClosure
     * @param array $options
     */
    public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
    }

    /**
     * @param IOutput $output
     * @param Closure(): ISchemaWrapper $schemaClosure
     * @param array $options
     * @return null|ISchemaWrapper
     */
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        if ($schema->hasTable('news_feeds')) {
            $table = $schema->getTable('news_feeds');
            $table->getColumn('title')->setNotnull(false);
        }

        return $schema;
    }

    /**
     * @param IOutput $output
     * @param Closure(): ISchemaWrapper $schemaClosure
     * @param array $options
     */
    public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
    }
}
