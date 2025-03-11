<?php

namespace app\modules\admin\models;

/**
 * Class MosruOrganizationsStat
 * @package app\modules\admin\models
 */
class MosruOrganizationsStat extends \app\models\db\statistic\MosruOrganizationsStat
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organization::class, ['id' => 'id_organization']);
    }
}
