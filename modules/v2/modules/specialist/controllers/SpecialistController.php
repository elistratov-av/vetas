<?php

namespace app\modules\v2\modules\specialist\controllers;

use app\common\components\rbac\Role;
use app\models\db\Organizations;
use app\models\db\Specialists;
use app\modules\v2\modules\specialist\models\SpecialistModel;
use app\modules\v2\modules\specialist\skeletons\specialist\SpecialistList;
use yii\web\BadRequestHttpException;
use app\modules\v2\modules\BaseController;
use yii\web\MethodNotAllowedHttpException;

/**
 * Class SpecialistController
 * @package app\modules\v2\modules\specialist\controllers
 */
class SpecialistController extends BaseController
{
    /**
     * Метод для вывода информации о специалисте
     * @see https://jira.altarix.ru/browse/VETAIS-1184
     * @see https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=102766608
     *
     * @param int $id
     * @return Specialists|array|\yii\db\ActiveRecord|null
     */
    public function actionGet(int $id)
    {
        $model = Specialists::find()
            ->where(['id' => $id])
            ->with(['organization' => function ($query1) {
                /* @var $query1 \yii\db\ActiveQuery */
                $query1->select([
                    'id',
                    'name',
                ]);
            }])
            ->one();

        $this->checkAccess($this->action->getUniqueId(), $model, $this->actionParams);

        return $model->toArray(['id', 'id_organization', 'id_user', 'expel_date'], ['organization']);
    }

    /**
     * Метод для вывода списка специалистов
     * @see https://jira.altarix.ru/browse/VETAIS-1183
     * @see https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=102766586
     *
     * @param array|null $filter
     * @param int $page
     * @param int $limit
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionList(array $filter = null, int $page = 1, int $limit = 10)
    {
        $this->validateFilter($filter);

        $this->checkAccess($this->action->getUniqueId(), null, ['filter' => $filter]);

        if ($page < 1) {
            throw new BadRequestHttpException('Параметр page некорректный');
        }
        if ($limit < 0) {
            throw new BadRequestHttpException('Параметр limit некорректный');
        }
        $specialistModel = new SpecialistModel();

        return [
            'result' => $specialistModel->getList($page, $limit, $filter)
        ];
    }

    /**
     * Метод для создания специалиста
     * @see https://jira.altarix.ru/browse/VETAIS-1191
     * @see https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=102766904
     *
     * @param string $f_fio
     * @param string $i_fio
     * @param string $o_fio
     * @param string $sex
     * @param string $birthday
     * @param string $expel_date
     * @param int $id_organization
     * @param int|null $photo
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public function actionCreate(
        string  $f_fio,
        string  $i_fio,
        string  $sex,
        int     $id_organization,
        ?string $birthday = null,
        ?string $expel_date = null,
        ?string $o_fio = null,
        int     $photo = null
    ): array
    {
        throw new MethodNotAllowedHttpException();

        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $specialistModel = new SpecialistModel();

        return $specialistModel->create($f_fio, $i_fio, $o_fio, $sex, $birthday, $expel_date, $id_organization, $photo);
    }

    /**
     * Метод для редактирования специалиста
     * @see https://jira.altarix.ru/browse/VETAIS-1191
     * @see https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=102766906
     *
     * @param int $id
     * @param string $f_fio
     * @param string $i_fio
     * @param string $o_fio
     * @param string $sex
     * @param string $birthday
     * @param string $expel_date
     * @param int $id_organization
     * @param int|null $photo
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     */
    public function actionEdit(
        int     $id,
        string  $f_fio,
        string  $i_fio,
        string  $sex,
        int     $id_organization,
        ?string $birthday = null,
        ?string $expel_date = null,
        ?string $o_fio = null,
        int     $photo = null
    ): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $specialistModel = new SpecialistModel();

        return $specialistModel->update($id, $f_fio, $i_fio, $o_fio, $sex, $birthday, $expel_date, $id_organization, $photo);
    }

    /**
     * Метод для удаления специалиста
     * @see https://jira.altarix.ru/browse/VETAIS-1191
     * @see https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=102766908
     *
     * @param int $id
     * @return array
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function actionDelete(int $id): array
    {
        throw new MethodNotAllowedHttpException();

        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $specialistModel = new SpecialistModel();

        return $specialistModel->delete($id);
    }

    /**
     * @param array|null $filter
     * @throws BadRequestHttpException
     */
    private function validateFilter(array &$filter = null): void
    {
        $this->validateIdOrganization($filter);
        if (\is_array($filter)) {
            $this->validateFio($filter);
            $this->validateIsFired($filter);
            $this->validateIdUser($filter);
        }
    }

    /**
     * @param $filter
     */
    private function validateIdOrganization(&$filter): void
    {
        if (\Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS)) {
            return;
        }

        if ($filter === null) {
            $filter = [];
        }
        if (!isset($filter['id_organization'])) {
            /* @var \app\common\models\UserModel $user */
            $user = \Yii::$app->user->getIdentity();
            $filter['id_organization'] = Organizations::orgTreeIds($user->specialist->id_organization);
        }
    }

    /**
     * @param $filter
     * @throws BadRequestHttpException
     */
    private function validateFio($filter): void
    {
        if (isset($filter['fio']) && \is_string($filter['fio']) === false) {
            throw new BadRequestHttpException('Параметр fio некорректный');
        }
    }

    /**
     * @param $filter
     * @throws BadRequestHttpException
     */
    private function validateIsFired($filter): void
    {
        if (isset($filter['is_fired']) && \is_bool($filter['is_fired']) === false) {
            throw new BadRequestHttpException('Параметр is_fired некорректный');
        }
    }

    /**
     * @param $filter
     * @throws BadRequestHttpException
     */
    private function validateIdUser($filter): void
    {
        if (isset($filter['id_user']) && is_numeric($filter['id_user']) === false) {
            throw new BadRequestHttpException('Параметр id_user некорректный');
        }
    }

    /**
     * Метод для вывода информации о роли специалиста
     * @param int $specId
     * @param int $orgId
     */
    public function actionRole(int $specId, int $orgId): bool
    {
           $model = Specialists::find()
               ->innerJoin('public.auth_assignment', 'public.auth_assignment.id_specialist = public.specialists.id')
               ->where([
                   'public.specialists.id' => $specId,
                   'public.specialists.id_organization' => $orgId,
                   'public.auth_assignment.item_name' => 'managementGos'
               ])
               ->one();
        return empty($model);
    }

}
