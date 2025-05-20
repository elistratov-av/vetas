<?php

namespace app\modules\v2\modules\visit\controllers;

use app\models\db\Visits;
use app\modules\v2\modules\specialist\models\DatelistModel;
use app\modules\v2\modules\specialist\models\SpecialistModel;
use app\modules\v2\modules\specialist\skeletons\specialist\Lists;
use app\modules\v2\modules\timesheet\skeletons\timesheet\Lists as DateLists;
use yii\web\BadRequestHttpException;
use app\modules\v2\modules\BaseController;

/**
 * Class SpecialistController
 * @package app\modules\v2\modules\specialist\controllers
 */
class SpecialistController extends BaseController
{
    /**
     * Метод выбора специалиста или специализации для записи в ЖО
     * https://jira.altarix.ru/browse/VETAIS-836
     *
     * @param int $id_organization
     *
     * @param int $id_shift_type
     * @param int $page
     * @param int $limit
     * @return Lists
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionTimelivequenelist(
        int $id_organization,
        int $id_shift_type,
        int $page = 1,
        int $limit = 10): Lists
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $specialistModel = new SpecialistModel();

        return $specialistModel->getTimeLiveQueneList($id_organization, $id_shift_type, $page, $limit);
    }

    /**
     * Метод выбора таймслотов специалиста
     *
     * @see https://jira.altarix.ru/browse/VETAIS-850
     * @see https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=96318510
     *
     * @param int    $id_organization
     * @param string $date_from
     * @param int    $id_specialist
     * @param int    $id_shift_type
     * @param array  $services
     * @param string $variety    разновидность приёма (MULTIPLE | BROOD | SINGLE)
     * @param string $type       тип приема (VISIT | AT_HOME)
     * @param int    $count_pets кол-во животных в приеме
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionTimelist(
        int $id_organization,
        string $date_from,
        int $id_specialist,
        int $id_shift_type,
        array $services,
        string $variety,
        string $type = Visits::TYPE_VISIT,
        int $count_pets = 1
    ): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $specialistModel = new SpecialistModel();
        $timeList = $specialistModel->getTimeList($id_organization, $date_from, $id_specialist, $id_shift_type, $services, $variety, $count_pets, $type);

        return ['result' => $timeList];
    }

    /**
     * Метод для страниц 'Расписание приема - запись по телефону' или 'Расписание приема - запись по направлению'
     * @see https://jira.altarix.ru/browse/VETAIS-848
     * @see https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=96318407
     *
     * @param int    $id_organization
     * @param int    $id_shift_type
     * @param string $date_from
     * @param string $type              тип приема (VISIT | AT_HOME)
     * @param int    $days_count
     * @param int    $page
     * @param int    $limit
     * @param int    $visit_id          id приёма, чтобы получить флаг is_assigned_specialist для спеца, который ранее был назначен на приём
     * @param array  $services          список идентификаторов услуг, которые были выбраны в рамках создания приема
     * @return DateLists
     * @throws BadRequestHttpException
     */
    public function actionDatelist(
        int $id_organization,
        int $id_shift_type,
        string $date_from,
        string $type = Visits::TYPE_VISIT,
        int $days_count = 14,
        int $page = 1,
        int $limit = 10,
        int $visit_id = null,
        array $services
    ): DateLists
    {

        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $dateListModel = new DatelistModel(compact('id_organization', 'id_shift_type', 'date_from', 'type', 'days_count', 'page', 'limit', 'visit_id', 'services'));

        if (!$dateListModel->validate()) {
            $errors = $dateListModel->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка валидации' : implode("\n", array_values($errors)));
        }

        return $dateListModel->list();
    }
}
