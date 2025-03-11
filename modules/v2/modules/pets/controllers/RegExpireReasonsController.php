<?php

namespace app\modules\v2\modules\pets\controllers;

use app\modules\v2\modules\BaseController;
use app\models\db\RegExpireReasons;

class RegExpireReasonsController extends BaseController
{
    /**
     * @see https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=102769601
     * @return array
     */
    public function actionList()
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => RegExpireReasons::find()->asArray()->all()
        ];
    }
}
