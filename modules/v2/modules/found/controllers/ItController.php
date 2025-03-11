<?php

namespace app\modules\v2\modules\found\controllers;

use app\models\db\found_pet\Ad;
use app\modules\foundPet\queue\JobHandler;
use app\modules\v2\modules\BaseController;
use yii\web\BadRequestHttpException;

/**
 * для тестирования
 *
 * Class ItController
 * @package app\modules\v2\modules\found\controllers
 */
class ItController extends BaseController
{

    /**
     * @param int $id
     * @return \app\models\db\found_pet\Ad
     * @throws \yii\web\BadRequestHttpException
     */
    private function findModel(int $id)
    {
        $model = Ad::findOne(['id' => $id]);

        if ($model === null) {
            throw new BadRequestHttpException('Объявление не найдено');
        }

        return $model;
    }

    /**
     * @return \app\modules\foundPet\queue\JobHandler
     */
    private function getJobHandler()
    {
        return new JobHandler();
    }

    public function actionAutoClose(int $id)
    {
        $model = $this->findModel($id);

        $model->is_active = false;
        $model->active_till = null;
        $model->closed_at = date('Y-m-d H:i:s');
        $model->closed_reason = 'Истек срок размещения объявления';
        $model->closed_by = Ad::CLOSED_AUTO;

        $result = $model->save(true, ['is_active', 'active_till', 'closed_at', 'closed_reason', 'closed_by', 'updated_at']);

        if ($result === true) {
            $this->getJobHandler()->adAutoCloseSuccess($model);
        }

        return [
            'result' => $result,
        ];
    }

    public function actionSend80211(int $id)
    {
        $model = $this->findModel($id);

        $this->getJobHandler()->sendStatus80211($model);

        $result = true;

        return [
            'result' => $result,
        ];
    }

    public function actionSend80212(int $id)
    {
        $model = $this->findModel($id);

        $this->getJobHandler()->sendStatus80212($model);

        $result = true;

        return [
            'result' => $result,
        ];
    }
}
