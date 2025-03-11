<?php

namespace app\modules\v2\modules\recoveryPassword\controllers;

use app\modules\v2\modules\recoveryPassword\models\RecoveryPasswordModel;
use app\modules\v2\modules\BaseController;
use yii\db\Exception;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;


class RecoveryPasswordController extends BaseController
{
    /**
     * @return array
     * @throws Exception
     * @throws ForbiddenHttpException
     */
    public function actionList(): array
    {
        $model = new RecoveryPasswordModel();

        return [
            $model->all(),
        ];
    }


    /**
     * @throws \yii\base\Exception
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionAdd($login)
    {
        $model = new RecoveryPasswordModel();

        return [
            'result' => $model->add($login),
        ];
    }

}
