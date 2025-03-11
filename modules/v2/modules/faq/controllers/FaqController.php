<?php


namespace app\modules\v2\modules\faq\controllers;

use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\faq\models\FaqModel;
use yii\web\BadRequestHttpException;

class FaqController extends BaseController
{

    /**
     * Возвращает список всех справок
     *
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionAll()
    {
        $model = new FaqModel();

        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => $model->all(),
        ];
    }

    /**
     * Создает новую справку
     *
     * @param $question
     * @param $answer
     * @param $links
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionCreate($question, $answer, $links = null)
    {
        $model = new FaqModel();

        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $faq = $model->create($question, $answer, $links);
        return [
            'result' => true,
            'id' => $faq->id
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
        $model = new FaqModel();

        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model->delete($id);
        return [
            'result' => true
        ];
    }

    /**
     * Редактирование справки
     *
     * @param $id
     * @param $question
     * @param $answer
     * @param $links
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionEdit($id, $question = null, $answer = null, $links = null)
    {
        $model = new FaqModel();

        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model->edit($id, $question, $answer, $links);
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
        $model = new FaqModel();

        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => $model->get($id)
        ];
    }
}
