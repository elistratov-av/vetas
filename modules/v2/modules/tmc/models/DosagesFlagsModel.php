<?php

namespace app\modules\v2\modules\tmc\models;

use app\models\db\tmc\Dosages;
use app\models\db\tmc\DosagesFlags;
use Exception;

/**
 * Флаги для дозировок
 * Class DosagesFlagsModel
 *
 * @package app\modules\v2\modules\tmc\models
 * @author Aleksandr Roik
 */
class DosagesFlagsModel
{
    /**
     * Переключает флаг "для актов" на указанную дозировку
     *
     * @param int $dosagesId
     */
    public function switchFlagForActToDosage(int $dosagesId)
    {
        $dosages = Dosages::findOne($dosagesId);
        if (!$dosages) {
            throw new Exception("Дозировка ID:$dosagesId не найдена");
        }

        DosagesFlags::updateAll([
            'for_act' => false,
        ], [
            'id_dosages' => Dosages::find()->where(['id_tmc' => $dosages->id_tmc])->select('id')->column()
        ]);

        $dosages->flag->setAttribute('for_act', true)->save();
    }

}
