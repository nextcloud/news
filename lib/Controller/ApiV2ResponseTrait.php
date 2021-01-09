<?php

/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Paul Tirk <paultirk@paultirk.com>
 * @copyright 2020 Paul Tirk
 */

namespace OCA\News\Controller;

use \OCP\AppFramework\Http;
use \OCP\AppFramework\Http\JSONResponse;

use \OCA\News\Db\IAPI;

trait ApiV2ResponseTrait
{
    /**
     * Serialize an entity
     *
     * @param IAPI $data
     *
     * @return array
     */
    public function serializeEntity($data, bool $reduced = false): array
    {
        return $data->toAPI2($reduced);
    }

    /**
     * Serialize array of entities
     *
     * @param array $data
     *
     * @return array
     */
    public function serializeEntities($data, bool $reduced = false): array
    {
        $return = [];
        foreach ($data as $entity) {
            $return[] = $entity->toAPI2($reduced);
        }
        return $return;
    }

    public function response($data, $code = Http::STATUS_OK)
    {
        return new JSONResponse($data, $code);
    }

    /**
     * @param \Exception $exception
     * @param int $code
     * @return \OCP\AppFramework\Http\JSONResponse
     */
    public function errorResponse(\Exception $exception, $code)
    {
        return new JSONResponse([
            'error' => [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage()
            ]
        ], $code);
    }
}
