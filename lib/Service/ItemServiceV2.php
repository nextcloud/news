<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Alessandro Cosentino <cosenal@gmail.com>
 * @author    Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright 2020 Sean Molenaar
 */

namespace OCA\News\Service;

use OCA\News\AppInfo\Application;
use OCA\News\Db\Item;
use OCA\News\Db\ItemMapperV2;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\IConfig;
use Psr\Log\LoggerInterface;

/**
 * Class ItemService
 *
 * @package OCA\News\Service
 */
class ItemServiceV2 extends Service
{

    /**
     * @var IConfig
     */
    protected $config;

    /**
     * ItemService constructor.
     *
     * @param ItemMapperV2    $mapper
     * @param IConfig          $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        ItemMapperV2 $mapper,
        IConfig $config,
        LoggerInterface $logger
    ) {
        parent::__construct($mapper, $logger);
        $this->config = $config;
    }

    /**
     * Finds all items of a user
     *
     * @param string $userId The ID/name of the user
     * @param array $params Filter parameters
     *
     *
     * @return Item[]
     */
    public function findAllForUser(string $userId, array $params = []): array
    {
        return $this->mapper->findAllFromUser($userId, $params);
    }

    /**
     * Find all items
     *
     * @return Item[]
     */
    public function findAll(): array
    {
        return $this->mapper->findAll();
    }

    /**
     * Insert an item or update.
     *
     * @param Item $item
     *
     * @return Entity|Item The updated/inserted item
     */
    public function insertOrUpdate(Item $item): Entity
    {
        try {
            $db_item = $this->mapper->findByGuidHash($item->getFeedId(), $item->getGuidHash());

            // Transfer user modifications
            $item->setUnread($db_item->isUnread())
                 ->setStarred($db_item->isStarred())
                 ->setId($db_item->getId());

            $item->generateSearchIndex();
            // We don't want to update the database record if there is no
            // change in the fetched item
            if ($db_item->getFingerprint() === $item->getFingerprint()) {
                $item->resetUpdatedFields();
            }

            return $this->mapper->update($item);
        } catch (DoesNotExistException $exception) {
            return $this->mapper->insert($item);
        }
    }

    /**
     * @param int $feedId
     *
     * @return array
     */
    public function findAllForFeed(int $feedId): array
    {
        return $this->mapper->findAllForFeed($feedId);
    }



    public function purgeOverThreshold(int $threshold = null)
    {

        $threshold = (int) $threshold ?? $this->config->getAppValue(
            Application::NAME,
            'autoPurgeCount',
            Application::DEFAULT_SETTINGS['autoPurgeCount']
        );

        if ($threshold === 0) {
            return '';
        }

        return $this->mapper->deleteOverThreshold($threshold);
    }

    /**
     * @param int    $feedId
     * @param string $guidHash
     */
    public function findForGuidHash(int $feedId, string $guidHash)
    {
        return $this->mapper->findByGuidHash($feedId, $guidHash);
    }
}
