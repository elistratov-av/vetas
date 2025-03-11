<?php

namespace app\modules\v2\modules\visit\controllers;

use app\modules\v2\modules\visit\models\DescriptionsTemplatesModel;
use app\modules\v2\modules\BaseController;

/**
 * Методы для работы с шаблонами описания приема (вкладка "Данные приема")
 *
 * Class DescriptionsTemplatesController
 * @package app\modules\v2\modules\visit\controllers
 */
class DescriptionsTemplatesController extends BaseController
{

    /**
     * Возвращает типы шаблонов
     *
     * @return array
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionTypes()
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new DescriptionsTemplatesModel())->getTypes(),
        ];
    }

    /**
     * Возвращает шаблон по его id
     *
     * @param $id
     * @return array
     * @throws \Throwable
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionGet($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new DescriptionsTemplatesModel())->getTemplate($id),
        ];
    }

    /**
     * Создание шаблона
     *
     * @param $template
     * @param $public
     * @param $caption
     * @param $template_type
     * @param $param_tech_name
     * @return array
     * @throws \Throwable
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionCreate($template, $public, $caption, $template_type, $param_tech_name = NULL)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $desc_template = (new DescriptionsTemplatesModel())->create(
            $template, $public, $caption, $template_type, $param_tech_name
        );

        return [
            'result' => true,
            'id' => $desc_template->id,
        ];
    }

    /**
     * Удаление шаблона
     *
     * @param $id
     * @return array
     * @throws \Throwable
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionDelete($id)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        (new DescriptionsTemplatesModel())->delete($id);
        return [
            'result' => true,
        ];
    }

    /**
     * Редактирование шаблона
     *
     * @param $id
     * @param $template
     * @param $public
     * @param $caption
     * @param $template_type
     * @param $param_tech_name
     * @return array
     * @throws \Throwable
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionEdit($id, $template, $public, $caption, $template_type, $param_tech_name = NULL)
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        (new DescriptionsTemplatesModel())->edit(
            $id,$template, $public, $caption, $template_type, $param_tech_name
        )
        ;
        return [
            'result' => true,
        ];
    }

    /**
     * Поиск шаблонов (ответ зависит от прав пользователя)
     *
     * @param int $page
     * @param int $limit
     * @param null $filter
     * @return array
     * @throws \Throwable
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionList($page = 1, $limit = 10, $filter = [])
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => (new DescriptionsTemplatesModel())->listTemplates($page, $limit, $filter)
        ];
    }

    /**
     * Возвращает список параметров "результаты исследования" с datatype text, которым можно назначить шаблоны
     * @return array
     */
    public function actionParamsList()
    {
        return [
            'result' => (new DescriptionsTemplatesModel())->paramsList(),
        ];
    }
}
