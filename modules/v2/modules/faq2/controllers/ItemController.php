<?php


namespace app\modules\v2\modules\faq2\controllers;

use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\faq2\models\Faq2Model;
use yii\web\BadRequestHttpException;

class ItemController extends BaseController
{

    /**
     * Возвращает список всех faq2
     *
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionAll()
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new Faq2Model();
        return [
            'result' => $model->all(),
        ];
    }

    /**
     * Создает новую справку
     *
     * @param $question
     * @param $answer
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionCreate($question, $answer)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new Faq2Model();
        $faq = $model->create($question, $answer);
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
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new Faq2Model();
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
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionEdit($id, $question = null, $answer = null)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new Faq2Model();
        $model->edit($id, $question, $answer);
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

        $model = new Faq2Model();
        return [
            'result' => $model->get($id)
        ];
    }

    /**
     * Поиск справок
     *
     * @param $question
     * @param $answer
     * @param $page
     * @param $limit
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionSearch(
        $question = null, $answer = null,
        $page = null, $limit = null
    )
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        if (is_null($page)) {
            $page = 1;
        }
        if (is_null($limit)) {
            $limit = 10;
        }
        $model = new Faq2Model();
        return [
            'result' => $model->search(
                $question, $answer,
                $page, $limit
            ),
        ];
    }
}
