<?php


namespace app\modules\v2\modules\services\controllers;

use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\services\models\CategoriesModel;

class CategoriesController extends BaseController
{
    public function actionGet($id_service)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $category = new CategoriesModel();

        return [
            'result' => $category->get($id_service)
        ];
    }

    public function actionSave($id_service, $category_ids)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $category = new CategoriesModel();
        $category->save($id_service, $category_ids);

        return [
            'result' => true
        ];
    }
}