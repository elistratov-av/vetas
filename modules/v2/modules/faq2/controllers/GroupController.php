<?php


namespace app\modules\v2\modules\faq2\controllers;

use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\faq2\models\Faq2GroupModel;
use app\modules\v2\modules\faq2\models\Faq2ToGroupModel;
use yii\web\BadRequestHttpException;

class GroupController extends BaseController
{

    /**
     * Возвращает список всех групп справок
     *
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionAll()
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new Faq2GroupModel();
        return [
            'result' => $model->all(),
        ];
    }

    /**
     * Создает новую группу справок
     *
     * @param $question
     * @param $answer
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionCreate($code, $title)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new Faq2GroupModel();
        $faq = $model->create($code, $title);
        return [
            'result' => true,
            'id' => $faq->id
        ];
    }

    /**
     * Удаляет группу справок
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

        $model = new Faq2GroupModel();
        $model->delete($id);
        return [
            'result' => true
        ];
    }

    /**
     * Редактирование группы справок
     *
     * @param $id
     * @param $code
     * @param $title
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Throwable
     */
    public function actionEdit($id, $code = null, $title = null)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new Faq2GroupModel();
        $model->edit($id, $code, $title);
        return [
            'result' => true
        ];
    }

    /**
     * Возвращает указанную группу справок
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

        $model = new Faq2GroupModel();
        return [
            'result' => $model->get($id)
        ];
    }

    /**
     * Возвращает указанную группу справок
     *
     * @param $code
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionGetByCode($code)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new Faq2GroupModel();
        return [
            'result' => $model->getByCode($code)
        ];
    }

    /**
     * Возвращает указанную справки, сопоставленные с группой справок
     *
     * @param $id
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionGetItems($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new Faq2GroupModel();
        return [
            'result' => $model->getFaq2($id)
        ];
    }

    /**
     * Добавляет указанную справку в группу справок
     *
     * @param $id_faq2_group
     * @param $id_faq2
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionAddItem($id_faq2_group, $id_faq2)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new Faq2ToGroupModel();
        return [
            'result' => $model->create($id_faq2, $id_faq2_group)
        ];
    }

    /**
     * Удаляет указанную справку из группы справок
     *
     * @param $id_faq2_group
     * @param $id_faq2
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionDelItem($id_faq2_group, $id_faq2)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $model = new Faq2ToGroupModel();
        return [
            'result' => $model->delete2($id_faq2_group, $id_faq2) > 0
        ];
    }

}
