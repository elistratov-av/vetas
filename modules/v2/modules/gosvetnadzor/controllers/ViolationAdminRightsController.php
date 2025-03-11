<?php


namespace app\modules\v2\modules\gosvetnadzor\controllers;

use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\gosvetnadzor\models\ViolationAdminRightsModel;

class ViolationAdminRightsController extends BaseController
{
    /**
     * Возвращает весь справочник АПН
     *
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionAll()
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new ViolationAdminRightsModel())->getAll()
        ];
    }

    /**
     * Создание записи в справочнике АПН
     *
     * @param $short_name
     * @param $full_name
     * @param $description
     * @return array
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionCreate($short_name, $full_name, $description)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $result = (new ViolationAdminRightsModel())->create($short_name, $full_name, $description);
        return [
            'result' => true,
            'id' => $result->id_ARV
        ];
    }

    /**
     * Возвращает АПН по id
     *
     * @param $id
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionGet($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new ViolationAdminRightsModel())->getById($id)
        ];
    }

    /**
     * Пометка "удалленное" для АПН
     *
     * @param $id
     * @return array
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionDelete($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        (new ViolationAdminRightsModel())->delete($id);
        return [
            'result' => true
        ];
    }

    /**
     * Редактирование АПН
     *
     * @param $id
     * @param $short_name
     * @param $full_name
     * @param $description
     * @return array
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionEdit($id, $short_name, $full_name, $description)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        (new ViolationAdminRightsModel())->edit($id, $short_name, $full_name, $description);
        return [
            'result' => true
        ];
    }


    /**
     * Список АПН
     *
     * @param int $page
     * @param int $limit
     * @param null $filter
     * @return array
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionList($page = 1, $limit = 10, $filter = null)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new ViolationAdminRightsModel())->getList($page, $limit, $filter)
        ];
    }

}
