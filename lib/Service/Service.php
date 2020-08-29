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
     * @var NewsMapper|NewsMapperV2
     */
    protected $mapper;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Service constructor.
     *
     * @param NewsMapper|NewsMapperV2 $mapper
     * @param LoggerInterface         $logger
     */
    public function __construct($mapper, LoggerInterface $logger)
    {
        $this->mapper = $mapper;
        $this->logger = $logger;
    }

    /**
     * Finds all items of a user
     *
     * @param string $userId the name of the user
     *
     * @return Entity[]
     */
    abstract public function findAllForUser(string $userId): array;

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
    public function delete(string $userId, int $id)
    {
        $entity = $this->find($userId, $id);

        $this->mapper->delete($entity);
    }


    /**
     * Finds an entity by id
     *
     * @param int    $id     the id of the entity
     * @param string $userId the name of the user for security reasons
     *
     * @return \OCP\AppFramework\Db\Entity the entity
     * @throws ServiceNotFoundException if the entity does not exist, or there
     * are more than one of it
     */
    public function find(string $userId, int $id): Entity
    {
        try {
            return $this->mapper->find($userId, $id);
        } catch (DoesNotExistException $ex) {
            throw new ServiceNotFoundException($ex->getMessage());
        } catch (MultipleObjectsReturnedException $ex) {
            throw new ServiceNotFoundException($ex->getMessage());
        }
    }
}
