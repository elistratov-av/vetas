<?php


namespace app\modules\v2\modules\organization\controllers;

use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\organization\models\ServicesWithoutMarkUpModel;

class ServicesWithoutMarkUpController extends BaseController
{

    /**
     * Возвращает список услуг без повышения стоимости ночью
     * @param $id_organization
     * @return array
     */
    public function actionGet($id_organization)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new ServicesWithoutMarkUpModel())->getByOrganization($id_organization)
        ];
    }

    /**
     * Сохранение списка услуг без повышения стоимости ночью
     *
     * @param $id_organization
     * @param $services
     * @return array
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSave($id_organization, $services)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        (new ServicesWithoutMarkUpModel())->save($id_organization, $services);
        return [
            'result' => true,
        ];
    }
}
