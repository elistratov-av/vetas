<?php

namespace app\modules\soap\models;

use yii\db\Expression;

/**
 * @property ServiceGoal $serviceGoal
 * @property ServiceTypes $serviceType;
 */
class MosruServices extends \app\models\db\MosRuServices
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServiceGoal()
    {
        return $this->hasOne(ServiceGoal::class, ['id' => 'id_service_goal']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServiceType()
    {
        return $this->hasOne(ServiceTypes::class, ['id' => 'id_service_type']);
    }

    /**
     * @param $service_ids
     * @return int
     */
    public static function getServicesDuration($service_ids)
    {
        return (int) self::find()
            ->select(new Expression("SUM(mosru.services.duration) AS duration"))
            ->where(['IN', 'mosru.services.id', $service_ids])
            ->scalar();
    }

    /**
     * @param $service_ids
     * @return int
     */
    public static function getServicesCooldown($service_ids)
    {
        return (int) self::find()
            ->select(new Expression("SUM(mosru.services.cooldown) AS cooldown"))
            ->where(['IN', 'mosru.services.id', $service_ids])
            ->scalar();
    }

    /**
     * @param $service_ids
     * @return int
     */
    public static function getServicesFullDuration($service_ids)
    {
        return (int) self::find()
            ->select(new Expression('SUM(mosru.services.duration + mosru.services.cooldown)'))
            ->where(['IN', 'mosru.services.id', $service_ids])
            ->scalar();
    }
}
