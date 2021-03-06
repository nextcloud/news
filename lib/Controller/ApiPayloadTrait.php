<?php


namespace OCA\News\Controller;

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
}
