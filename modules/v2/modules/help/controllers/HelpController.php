<?php


namespace app\modules\v2\modules\help\controllers;

use app\common\validators\FullTrimValidator;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\help\models\HelpModel;
use yii\web\BadRequestHttpException;

class HelpController extends BaseController
{

    /**
     * Возвращает список всех справок
     *
     * @param $search_text
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionAll($search_text = null)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new HelpModel();
        $fullTrimValidator = new FullTrimValidator();

        return [
            'result' => $model->all($fullTrimValidator->validateValue($search_text)),
        ];
    }

    /**
     * Создает новую справку
     *
     * @param $caption
     * @param $text
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionCreate($caption, $text)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new HelpModel();

        $help = $model->create($caption, $text);
        return [
            'result' => true,
            'id' => $help->id
        ];
    }

    /**
     * Удаляет справку
     *
     * @param $id
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionDelete($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new HelpModel();

        $model->delete($id);
        return [
            'result' => true
        ];
    }

    /**
     * Редактирование справки
     *
     * @param $id
     * @param $caption
     * @param $text
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionEdit($id, $caption = null, $text = null)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new HelpModel();

        $model->edit($id, $caption, $text);
        return [
            'result' => true
        ];
    }

    /**
     * Возвращает указанную справку
     *
     * @param $id
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionGet($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new HelpModel();

        return [
            'result' => $model->get($id)
        ];
    }
}
