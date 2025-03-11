<?php


namespace app\modules\v2\modules\tmc\controllers;


use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\tmc\models\ExpMaterialsModel;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

class ExpMaterialsController extends BaseController
{
    /**
     * @param int $id
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionGet(int $id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new ExpMaterialsModel())->getExpMaterial($id)
        ];
    }

    /**
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @return array
     * @throws ForbiddenHttpException
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     */
    public function actionList(int $page = 1, int $limit = 10, array $filter = [])
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);
        $expMaterials = new ExpMaterialsModel();

        return [
            'result' => $expMaterials->list($page, $limit, $filter)
        ];
    }

    /**
     * @param $name
     * @param $id_measure
     * @param null $description
     * @param int[] $category_ids
     * @param bool $is_uncountable
     * @return array ray
     * ray
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionCreate($name, $id_measure, $description = null, $category_ids = [], $is_uncountable = false)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);
        $model = new ExpMaterialsModel();
        $expMaterial = $model->create($name, $id_measure, $description, $category_ids, $is_uncountable);

        return [
            'result' => true,
            'id' => $expMaterial->id
        ];
    }

    /**
     * @param $id
     * @param $name
     * @param $id_measure
     * @param $description
     * @param array $category_ids
     * @param bool $is_uncountable
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionEdit($id, $name, $id_measure, $description, $category_ids = [], $is_uncountable = false)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);
        (new ExpMaterialsModel())->edit($id, $name, $id_measure, $description, $category_ids, $is_uncountable);

        return [
            'result' => true
        ];
    }

    /**
     * @param $id
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        (new ExpMaterialsModel())->delete($id);

        return [
            'result' => true,
        ];
    }

}