<?php

namespace app\modules\soap\queue;

use yii\queue\JobInterface;
use yii\queue\serializers\PhpSerializer;

class ETPJobSerializer extends PhpSerializer
{
    /**
     * @param array|JobInterface|string $job
     * @return string
     */
    public function serialize($job)
    {
        if (is_array($job)) {
            return json_encode($job);
        }

        if ($job instanceof JobInterface) {
            return parent::serialize($job);
        }

        return $job;
    }

    /**
     * @inheritdoc
     */
    public function unserialize($serialized)
    {
        try {
            $data = parent::unserialize($serialized);

            if ($data instanceof JobInterface) {
                return $data;
            }
        } catch (\Exception $e) {
            $data = json_decode($serialized);
        }

        return new ETPChangeStatusJob([
            'visit_id' => $data->visit_id,
            'etp_status' => $data->etp_status ?? null,
            'status_id' => $data->status_id ?? null
        ]);
    }
}
