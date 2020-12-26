<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Alessandro Cosentino <cosenal@gmail.com>
 * @author    Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright 2012 Alessandro Cosentino
 * @copyright 2012-2014 Bernhard Posselt
 */

namespace OCA\News\Service;

use OCA\News\Db\NewsMapper;
use OCA\News\Db\NewsMapperV2;
use OCA\News\Service\Exceptions\ServiceNotFoundException;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use Psr\Log\LoggerInterface;

/**
 * Class Service
 *
 * @package OCA\News\Service
 */
abstract class Service
{
    /**
     * @var NewsMapperV2
     */
    protected $mapper;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Service constructor.
     *
     * @param NewsMapperV2    $mapper
     * @param LoggerInterface $logger
     */
    public function __construct(NewsMapperV2 $mapper, LoggerInterface $logger)
    {
        $this->mapper = $mapper;
        $this->logger = $logger;
    }

    /**
     * Finds all items of a user
     *
     * @param string $userId The ID/name of the user
     * @param array  $params Filter parameters
     *
     * @return Entity[]
     */
    abstract public function findAllForUser(string $userId, array $params = []): array;

    /**
     * Finds all items
     *
     * @return Entity[]
     */
    abstract public function findAll(): array;


    /**
     * Delete an entity
     *
     * @param int    $id     the id of the entity
     * @param string $userId the name of the user for security reasons
     *
     * @throws ServiceNotFoundException if the entity does not exist, or there
     * are more than one of it
     */
    public function delete(string $userId, int $id): Entity
    {
        $entity = $this->find($userId, $id);

        return $this->mapper->delete($entity);
    }


    /**
     * Insert an entity
     *
     * @param Entity $entity The entity to insert
     *
     * @return Entity The inserted entity
     */
    public function insert(Entity $entity): Entity
    {
        return $this->mapper->insert($entity);
    }


    /**
     * Update an entity
     *
     * @param string $userId the name of the user for security reasons
     * @param Entity $entity the entity
     *
     * @throws ServiceNotFoundException if the entity does not exist, or there
     * are more than one of it
     */
    public function update(string $userId, Entity $entity): Entity
    {
        $this->find($userId, $entity->getId());
        return $this->mapper->update($entity);
    }


    /**
     * Finds an entity by id
     *
     * @param int    $id     the id of the entity
     * @param string $userId the name of the user for security reasons
     *
     * @return Entity the entity
     * @throws ServiceNotFoundException if the entity does not exist, or there
     * are more than one of it
     */
    public function find(string $userId, int $id): Entity
    {
        try {
            return $this->mapper->findFromUser($userId, $id);
        } catch (DoesNotExistException $ex) {
            throw new ServiceNotFoundException($ex->getMessage());
        } catch (MultipleObjectsReturnedException $ex) {
            throw new ServiceNotFoundException($ex->getMessage());
        }
    }

    /**
     * Delete all items of a user
     *
     * @param string $userId User ID/name
     */
    public function deleteUser(string $userId): void
    {
        $items = $this->findAllForUser($userId);
        foreach ($items as $item) {
            $this->mapper->delete($item);
        }
    }
}
