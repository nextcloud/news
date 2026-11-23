<?php
declare(strict_types=1);

/**
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 */
namespace OCA\News\Service;

use OCA\News\Db\ItemMapperV2;
use OCA\News\Db\ListType;
use OCA\News\Service\Exceptions\ServiceValidationException;
use OCP\AppFramework\Http\StreamResponse;
use OCP\IDBConnection;

class UpdatedItemsResponse
{
    public function __construct(
        private ItemMapperV2 $mapper,
        private IDBConnection $db
    ) {
    }

    public function createResponse(string $userId, int $type, int $id, int $updatedSince): StreamResponse
    {
        $types = [ListType::FEED, ListType::FOLDER, ListType::STARRED, ListType::ALL_ITEMS, ListType::UNREAD];
        if (!in_array($type, $types, true)) {
            throw new ServiceValidationException('Trying to find in unknown type');
        }
        $provider = $this->db->getDatabaseProvider();
        $providers = [
            IDBConnection::PLATFORM_POSTGRES,
            IDBConnection::PLATFORM_MYSQL,
            IDBConnection::PLATFORM_MARIADB,
            IDBConnection::PLATFORM_SQLITE,
        ];
        if (!in_array($provider, $providers, true) || $this->db->inTransaction()) {
            throw new \RuntimeException('News sync requires its own database snapshot');
        }
        $stream = $this->createSpool();
        if ($stream === false) {
            throw new \RuntimeException('Could not create private News response spool');
        }
        $ownsTransaction = false;
        try {
            // MySQL's unqualified SET TRANSACTION applies to the next transaction.
            // PostgreSQL requires it after BEGIN, before the first query. SQLite
            // keeps its read snapshot from the first SELECT until COMMIT.
            if (in_array($provider, [IDBConnection::PLATFORM_MYSQL, IDBConnection::PLATFORM_MARIADB], true)) {
                $this->db->executeStatement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ, READ ONLY');
            }
            $this->db->beginTransaction();
            $ownsTransaction = true;
            if ($provider === IDBConnection::PLATFORM_POSTGRES) {
                $this->db->executeStatement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ, READ ONLY');
            }
            $this->writeAll($stream, '{"items":[');
            $beforeId = null;
            $first = true;
            do {
                $batch = $this->mapper->findUpdatedBatch($userId, $type, $id, $updatedSince, $beforeId, 100);
                $count = count($batch);
                foreach ($batch as $item) {
                    $itemId = $item->getId();
                    if ($beforeId !== null && $itemId >= $beforeId) {
                        throw new \RuntimeException('News sync cursor did not advance');
                    }
                    // Match Nextcloud JSONResponse flags and depth exactly.
                    $encoded = json_encode($item->toAPI(), JSON_HEX_TAG | JSON_THROW_ON_ERROR, 2048);
                    $this->writeAll($stream, ($first ? '' : ',') . $encoded);
                    $first = false;
                    $beforeId = $itemId;
                    unset($encoded);
                }
                unset($item, $batch);
            } while ($count === 100);
            $this->writeAll($stream, ']}');
            if (!fflush($stream) || !rewind($stream)) {
                throw new \RuntimeException('Could not finalize News response spool');
            }
            $stat = fstat($stream);
            if ($stat === false) {
                throw new \RuntimeException('Could not measure News response spool');
            }
            $size = $stat['size'];
            $this->db->commit();
            $ownsTransaction = false;
            // No body is transmitted until the complete JSON document is ready.
            // The resource is deleted automatically when its final handle closes.
            return new StreamResponse($stream, 200, [
                'Content-Type' => 'application/json; charset=utf-8',
                'Content-Length' => (string)$size,
                'Cache-Control' => 'private, no-store',
            ]);
        } catch (\Throwable $e) {
            if ($ownsTransaction) {
                try {
                    $this->db->rollBack();
                } catch (\Throwable $ignored) {
                    // Preserve the original failure; the request never returns a response.
                }
            }
            fclose($stream);
            throw $e;
        }
    }

    /** @return resource|false */
    protected function createSpool()
    {
        return tmpfile();
    }

    /** @param resource $stream */
    private function writeAll($stream, string $data): void
    {
        $offset = 0;
        $length = strlen($data);
        while ($offset < $length) {
            $written = fwrite($stream, substr($data, $offset));
            if ($written === false || $written === 0) {
                throw new \RuntimeException('Could not write News response spool');
            }
            $offset += $written;
        }
    }
}
