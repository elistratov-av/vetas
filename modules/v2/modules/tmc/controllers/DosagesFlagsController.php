<?php

namespace app\modules\v2\modules\tmc\controllers;

use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\tmc\models\DosagesFlagsModel;
use app\modules\v2\modules\tmc\models\DosagesModel;

/**
 * Class DosagesFlagsController
 *
 * @package app\modules\v2\modules\tmc\controllers
 */
class DosagesFlagsController extends BaseController
{
    /**
     * Установка флага: для акта
     *
     * @param int $id_dosages
     */
    public function actionSetFlagForAct(int $id_dosages)
    {
        $this->checkAccess($this->action->getUniqueId(), new DosagesModel(), $this->actionParams);
        (new DosagesFlagsModel())->switchFlagForActToDosage($id_dosages);

        return [
            'result' => true,
        ];
    }
}
