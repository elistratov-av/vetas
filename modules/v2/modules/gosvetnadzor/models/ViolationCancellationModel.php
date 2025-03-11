<?php


namespace app\modules\v2\modules\gosvetnadzor\models;


use app\models\db\ViolationCancellation;

class ViolationCancellationModel
{
    /**
     * Возвращает справочник не системных причин отмены работы над нарушением
     *
     * @return ViolationCancellation[]
     */
    public function getAllNonSystem()
    {
        return ViolationCancellation::find()
            ->where([
                'OR',
                ['IN', 'violation_cancellation.tech_name', ViolationCancellation::PUBLIC_TECH_NAMES],
                ['violation_cancellation.tech_name' => null]
            ])
            ->andWhere(['is_deleted' => false])
            ->orderBy('description')
            ->all();
    }

}
