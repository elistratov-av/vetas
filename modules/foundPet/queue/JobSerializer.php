<?php

namespace app\modules\foundPet\queue;

use yii\helpers\Json;
use yii\queue\serializers\JsonSerializer;

/**
 * Class JobSerializer
 * @package app\modules\foundPet\queue
 */
class JobSerializer extends JsonSerializer
{
    /**
     * @inheritDoc
     */
    public function serialize($job)
    {
        return $this->toArray($job);
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        $data = !is_array($serialized) ? Json::decode($serialized) : $serialized;

        return new SendStatusJob($data);
    }
}
