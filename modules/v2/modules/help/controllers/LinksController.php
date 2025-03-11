<?php


namespace app\modules\v2\modules\help\controllers;


use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\help\models\HelpLinkModel;
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
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new HelpLinkModel();

        return [
            'result' => $model->get($id)
        ];
    }

    /**
     * Возвращает список ссылок указанного faq
     *
     * @param $id_help
     * @return array
     * @throws \Throwable
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionList($id_help)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new HelpLinkModel();

        return [
            'result' => $model->list($id_help)
        ];
    }

    /**
     * Создает новую ссылку
     *
     * @param $href
     * @param $text
     * @param $id_help
     * @param $target_blank
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionCreate($href, $text, $id_help, $target_blank = null)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new HelpLinkModel();

        $faq_link = $model->create($href, $text, $id_help, $target_blank);
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
     * @param $target_blank
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionEdit($id, $href = null, $text = null, $target_blank = null)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new HelpLinkModel();

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
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new HelpLinkModel();

        $model->delete($id);
        return [
            'result' => true
        ];
    }
}
