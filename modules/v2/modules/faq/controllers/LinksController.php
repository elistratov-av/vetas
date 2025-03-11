<?php


namespace app\modules\v2\modules\faq\controllers;


use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\faq\models\FaqLinkModel;
use yii\web\BadRequestHttpException;

class LinksController extends BaseController
{
    /**
     * Возвращает указанную ссылку
     *
     * @param $id
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionGet($id)
    {
        $model = new FaqLinkModel();

        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => $model->get($id)
        ];
    }

    /**
     * Возвращает список ссылок указанного faq
     *
     * @param $id_faq
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionList($id_faq)
    {
        $model = new FaqLinkModel();

        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => $model->list($id_faq)
        ];
    }

    /**
     * Создает новую ссылку
     *
     * @param $href
     * @param $text
     * @param $id_faq
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionCreate($href = null, $text, $id_faq, $target_blank = null)
    {
        $model = new FaqLinkModel();

        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $faq_link = $model->create($href, $text, $id_faq, $target_blank);
        return [
            'result' => true,
            'id' => $faq_link->id
        ];
    }

    /**
     * Редактирование ссылки
     *
     * @param $id
     * @param $href
     * @param $text
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionEdit($id, $href = null, $text = null, $target_blank = null)
    {
        $model = new FaqLinkModel();

        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model->edit($id, $href, $text, $target_blank);
        return [
            'result' => true
        ];
    }

    /**
     * Удаляет ссылку
     *
     * @param $id
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionDelete($id)
    {
        $model = new FaqLinkModel();

        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model->delete($id);
        return [
            'result' => true
        ];
    }
}
