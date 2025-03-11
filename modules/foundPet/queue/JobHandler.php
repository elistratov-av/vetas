<?php

namespace app\modules\foundPet\queue;

use app\modules\foundPet\etp\status\Status103099;
use app\modules\foundPet\etp\status\Status1050;
use app\modules\foundPet\etp\status\Status1051;
use app\modules\foundPet\etp\status\Status1053;
use app\modules\foundPet\etp\status\Status106899;
use app\modules\foundPet\etp\status\Status1075_1;
use app\modules\foundPet\etp\status\Status1075_2;
use app\modules\foundPet\etp\status\Status1075_3;
use app\modules\foundPet\etp\status\Status1080;
use app\modules\foundPet\etp\status\Status801199;
use app\modules\foundPet\etp\status\Status8021_1;
use app\modules\foundPet\etp\status\Status8021_2;

/**
 * Class JobHandler
 * @package app\modules\foundPet\queue
 */
class JobHandler
{
    private CONST  ADDITIONAL_CLASS_ATTRIBUTES_ARRAY_KEY = 'attributes';

    /**
     * @return \app\modules\foundPet\queue\Queue
     * @throws \yii\base\InvalidConfigException
     */
    protected function getQueue()
    {
        /** @var \app\modules\foundPet\queue\Queue $queue */
        $queue = \Yii::$app->get('found_pet_queue');

        return $queue;
    }

    /**
     * @param \app\models\db\found_pet\Ad $ad
     * @param array|null                  $data
     * @throws \yii\base\InvalidConfigException
     */
    public function adCreateSuccess($ad, $data = null)
    {
        $statusCodes = [Status1050::CODE];

        $this->getQueue()->push([
            'statusCodes' => $statusCodes,
            'data' => $this->prepareStatusData($statusCodes, $ad, $data),
            'serviceNumber' => $ad->service_number,
        ]);
    }

    /**
     * @param array|null $data
     * @throws \yii\base\InvalidConfigException
     */
    public function adCreateError($data)
    {
        $statusCodes = [Status103099::CODE];

        $this->getQueue()->push([
            'statusCodes' => $statusCodes,
            'data' => $this->prepareStatusData($statusCodes, null, $data),
            'serviceNumber' => (isset($data['service_number']) ? $data['service_number'] : null),
        ]);
    }

    /**
     * @param \app\models\db\found_pet\Ad $ad
     * @param array|null                  $data
     * @throws \yii\base\InvalidConfigException
     */
    public function adUpdateSuccess($ad, $data = null)
    {
        $statusCodes = [Status1053::CODE];

        $this->getQueue()->push([
            'statusCodes' => $statusCodes,
            'data' => $this->prepareStatusData($statusCodes, $ad, $data),
            'serviceNumber' => $ad->service_number,
        ]);
    }

    /**
     * @param array|null $data
     * @throws \yii\base\InvalidConfigException
     */
    public function adUpdateError($data)
    {
        $statusCodes = [Status106899::CODE];

        $this->getQueue()->push([
            'statusCodes' => $statusCodes,
            'data' => $this->prepareStatusData($statusCodes, null, $data),
            'serviceNumber' => (isset($data['service_number']) ? $data['service_number'] : null),
        ]);
    }

    /**
     * @param \app\models\db\found_pet\Ad $ad
     * @param array|null                  $data
     * @throws \yii\base\InvalidConfigException
     */
    public function adExtendSuccess($ad, $data = null)
    {
        $statusCodes = [Status1051::CODE];

        $this->getQueue()->push([
            'statusCodes' => $statusCodes,
            'data' => $this->prepareStatusData($statusCodes, $ad, $data),
            'serviceNumber' => $ad->service_number,
        ]);
    }

    /**
     * @param array|null $data
     * @throws \yii\base\InvalidConfigException
     */
    public function adExtendError($data)
    {
        $statusCodes = [Status801199::CODE];

        $this->getQueue()->push([
            'statusCodes' => $statusCodes,
            'data' => $this->prepareStatusData($statusCodes, null, $data),
            'serviceNumber' => (isset($data['service_number']) ? $data['service_number'] : null),
        ]);
    }

    /**
     * @param \app\models\db\found_pet\Ad $ad
     * @param array|null                  $data
     * @throws \yii\base\InvalidConfigException
     */
    public function adCloseSuccess($ad, $data = null)
    {
        if (isset($data['reason'])) {
            $reason = trim(mb_strtolower($data['reason']));
            switch ($reason) {
                case 'животное найдено':
                case 'владелец найден':
                case '1':
                    $code = Status1075_1::CODE;
                    break;
                case 'другая причина':
                case '2':
                default:
                    $code = Status1075_2::CODE;
                    break;
            }
        } else {
            $code = Status1075_2::CODE;
        }

        $statusCodes = [(string)$code];

        $this->getQueue()->push([
            'statusCodes' => $statusCodes,
            'data' => $this->prepareStatusData($statusCodes, $ad, $data),
            'serviceNumber' => $ad->service_number,
        ]);
    }

    /**
     * @param \app\models\db\found_pet\Ad $ad
     * @param array|null                  $data
     * @throws \yii\base\InvalidConfigException
     */
    public function adAutoCloseSuccess($ad, $data = null)
    {
        $statusCodes = [(string)Status1075_3::CODE];

        $this->getQueue()->push([
            'statusCodes' => $statusCodes,
            'data' => $this->prepareStatusData($statusCodes, $ad, $data),
            'serviceNumber' => $ad->service_number,
        ]);
    }

    /**
     * @param array|null $data
     * @throws \yii\base\InvalidConfigException
     */
    public function adCloseError($data)
    {
        $statusCodes = [Status801199::CODE];

        $this->getQueue()->push([
            'statusCodes' => $statusCodes,
            'data' => $this->prepareStatusData($statusCodes, null, $data),
            'serviceNumber' => (isset($data['service_number']) ? $data['service_number'] : null),
        ]);
    }

    /**
     * @param \app\models\db\found_pet\Ad $ad
     * @param array|null                  $data
     * @throws \yii\base\InvalidConfigException
     */
    public function adModerationCloseSuccess($ad, $data = null)
    {
        $statusCodes = [Status1080::CODE];

        $this->getQueue()->push([
            'statusCodes' => $statusCodes,
            'data' => $this->prepareStatusData($statusCodes, $ad, $data),
            'serviceNumber' => $ad->service_number,
        ]);
    }

    /**
     * @param \app\models\db\found_pet\Ad $ad
     * @param \app\models\db\found_pet\Ad $ad_for_subscriber
     * @param array|null                  $data
     * @throws \yii\base\InvalidConfigException
     */
    public function sendStatus80211($ad, $ad_for_subscriber, $data = null)
    {
        $statusCodes = [(string)Status8021_1::CODE];

        $data[self::ADDITIONAL_CLASS_ATTRIBUTES_ARRAY_KEY] = ['ad_for_subscriber' => $ad_for_subscriber];
        $this->getQueue()->push([
            'statusCodes' => $statusCodes,
            'data' => $this->prepareStatusData($statusCodes, $ad, $data),
            'serviceNumber' => $ad->service_number,
        ]);
    }

    /**
     * @param \app\models\db\found_pet\Ad $ad
     * @param array|null                  $data
     * @throws \yii\base\InvalidConfigException
     */
    public function sendStatus80212($ad, $data = null)
    {
        $statusCodes = [(string)Status8021_2::CODE];

        $this->getQueue()->push([
            'statusCodes' => $statusCodes,
            'data' => $this->prepareStatusData($statusCodes, $ad, $data),
            'serviceNumber' => $ad->service_number,
        ]);
    }

    /**
     * @param array                            $statusCodes
     * @param \app\models\db\found_pet\Ad|null $ad
     * @param array|null                       $requestData
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    private function prepareStatusData(array $statusCodes, \app\models\db\found_pet\Ad $ad = null, array $requestData = null)
    {
        $fields = array_fill_keys($statusCodes, null);

        foreach ($fields as $statusCode => $value) {
            $className = '\app\modules\foundPet\etp\status\Status' . str_replace('.', '_', $statusCode);
            $attributes = [
                'class' => $className,
                'ad' => $ad,
                'ServiceNumber' => (isset($ad) ? $ad->service_number : (isset($requestData['service_number']) ? $requestData['service_number'] : null)),
            ];

            // Дополнительные поля
            if (!empty($requestData[self::ADDITIONAL_CLASS_ATTRIBUTES_ARRAY_KEY])){
                $attributes = array_merge($attributes, $requestData[self::ADDITIONAL_CLASS_ATTRIBUTES_ARRAY_KEY]);
            }
            /* @var $status \app\modules\foundPet\etp\status\Status */
            $status = \Yii::createObject($attributes);
            $fields[$statusCode] = $status->prepareRequestData();
        }

        return $fields;
    }
}
