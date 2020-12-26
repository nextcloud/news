<?php

namespace OCA\News\Controller;

use \OCP\AppFramework\Http;
use \OCP\AppFramework\Http\JSONResponse;

use \OCA\News\Db\IAPI;

trait ApiV2ResponseTrait
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
        if ($data instanceof IAPI) {
            return $data->toAPI2();
        }

        $return = [];
        if (!is_array($data)) {
            return $return;
        }

        foreach ($data as $entity) {
            if ($entity instanceof IAPI) {
                $return[] = $entity->toAPI2();
            }
        }
        return $return;
    }

    public function response($data, $code=Http::STATUS_OK)
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
