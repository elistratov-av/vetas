<?php

namespace app\modules\v2\modules\visit\controllers;

use app\modules\v2\modules\visit\models\SpecializationsModel;
use yii\web\BadRequestHttpException;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\visit\skeletons\specializations\Lists;

/**
 * Class SpecializationsController
 * работа со списком специалистов для записи в ЖО
 * https://jira.altarix.ru/browse/VETAIS-835
 *
 * @package app\modules\v2\modules\visit\controllers
 */
class SpecializationsController extends BaseController
{
    /**
     * По состоянию на 14.03.2019 фактически не используется на фронте, пользуются v1
     *
     * возвращает список специалистов для записи в ЖО
     * https://jira.altarix.ru/browse/VETAIS-835
     *
     * @param int $id_organization
     * @param int $page
     * @param int $limit
     * @return Lists
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public function actionList(int $id_organization, int $page = 1, $limit = 10): Lists
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        if ($limit <= 0) {
            throw new BadRequestHttpException('Параметр limit должен быть больше нуля');
        }
        if ($page <= 0) {
            throw new BadRequestHttpException('Параметр page должен быть больше нуля');
        }
        $specializationsModel = new SpecializationsModel();
        return $specializationsModel->list($id_organization, $page, $limit);
    }


    /**
     * возвращает список всех специализаций
     * https://jira.altarix.ru/browse/VETAIS-1395
     *
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionAll(): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return ['result' => (new SpecializationsModel())->all()];
    }
}
