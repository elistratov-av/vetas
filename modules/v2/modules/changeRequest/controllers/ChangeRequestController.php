<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 25.07.19
 * Time: 12:12
 */

namespace app\modules\v2\modules\changeRequest\controllers;

use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\changeRequest\models\ChangeRequestModel;

class ChangeRequestController extends BaseController
{
    /**
     *
     * Создает новый запрос
     *
     * @param $entity_name
     * @param $type
     * @param string $description
     * @return array
     * @throws \Throwable
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionCreate($entity_name, $type, $description = null)
    {
        $model = new ChangeRequestModel();

        $this->checkAccess($this->action->getUniqueId(), $model, $this->actionParams);

        $model->create($entity_name, $type, $description);
        return [
            'result' => true,
        ];
    }
}