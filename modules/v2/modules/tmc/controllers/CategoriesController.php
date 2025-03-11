<?php


namespace app\modules\v2\modules\tmc\controllers;

use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\tmc\models\CategoryModel;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;

class CategoriesController extends BaseController
{
    /**
     * @param int $id
     * @return array
     * @throws ForbiddenHttpException|BadRequestHttpException
     */
    public function actionGet(int $id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new CategoryModel())->getCategory($id)
        ];
    }

    /**
     * Список категорий
     *
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
        $categories = new CategoryModel();

        return [
            'result' => $categories->list($page, $limit, $filter)
        ];
    }

    /**
     * Создание категории
     *
     * @param $name
     * @param null $description
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionCreate($name, $description)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);
        $model = new CategoryModel();
        $categories = $model->create($name, $description);

        return [
            'result' => true,
            'id' => $categories->id
        ];
    }

    /**
     * Редактирование категории
     *
     * @param $id
     * @param $name
     * @param null $description
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     */
    public function actionEdit($id, $name, $description)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);
        (new CategoryModel())->edit($id, $name, $description);

        return [
            'result' => true
        ];
    }

    /**
     * Удаление категории
     *
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

        (new CategoryModel())->delete($id);

        return [
            'result' => true,
        ];
    }
}