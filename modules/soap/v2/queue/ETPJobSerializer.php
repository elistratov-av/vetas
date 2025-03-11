<?php

namespace app\modules\soap\v2\queue;

use yii\helpers\Json;
use yii\queue\JobInterface;
use yii\queue\serializers\PhpSerializer;

/**
 * Class ETPJobSerializer
 * @package app\modules\soap\v2\queue
 */
class ETPJobSerializer extends PhpSerializer
{
    /**
     * @param array|JobInterface|string $job
     * @return string
     */
    public function serialize($job)
    {
        if (is_array($job)) {
            return Json::encode($job);
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
            $data = Json::decode($serialized, false);
        }

        $config = [
            'etp_status' => $data->etp_status ?? null,
            'status_id' => $data->status_id ?? null,
            'error_message' => $data->error_message ?? null,
            'note' => $data->note ?? null,
            'payment_url' => $data->payment_url ?? null,
        ];

        if (!empty($data->service_number)) {
            $config['service_number'] = $data->service_number;
            $job = new ETPGeneralErrorJob($config);
        } else {
            $config['visit_id'] = $data->visit_id;
            $job = new ETPChangeStatusJob($config);
        }

        return $job;
    }
}
