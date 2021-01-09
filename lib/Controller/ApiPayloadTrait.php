<?php

namespace OCA\News\Controller;

use \OCP\AppFramework\Http;
use \OCP\AppFramework\Http\JSONResponse;

use OCA\News\Db\IAPI;

trait ApiPayloadTrait
{
    /**
     * Serialize all data
     *
     * @param mixed $data IAPI or array,
     *                    anything else will return an empty array
     *
     * @return array
     */
    public function serialize($data): array
    {
        $return = [];
        if ($data instanceof IAPI) {
            return [$data->toAPI()];
        }

        if (!is_array($data)) {
            return $return;
        }

        foreach ($data as $entity) {
            if ($entity instanceof IAPI) {
                $return[] = $entity->toAPI();
            }
        }
        return $return;
    }

    /**
     * Serialize an entity
     *
     * @param IAPI $data
     *
     * @return array
     */
    public function serializeEntityV2($data, bool $reduced = false): array
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
    public function serializeEntitiesV2($data, bool $reduced = false): array
    {
        $return = [];
        foreach ($data as $entity) {
            $return[] = $entity->toAPI2($reduced);
        }
        return $return;
    }

    public function responseV2($data, $code = Http::STATUS_OK)
    {
        return new JSONResponse($data, $code);
    }
}
