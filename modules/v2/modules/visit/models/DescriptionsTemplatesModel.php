<?php


namespace app\modules\v2\modules\visit\models;

use app\common\validators\FullTrimValidator;
use app\models\db\Params;
use Yii;
use app\common\components\rbac\rules\UserAllOrgsRule;
use app\common\components\rbac\rules\UserOrgTreeRule;
use app\common\components\rbac\Role;
use app\common\models\UserModel;
use app\models\db\DescriptionsTemplates;
use app\modules\v2\common\skeletons\CommonList;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;


class DescriptionsTemplatesModel
{
    /**
     *
     *
     * @return array
     */
    public function getTypes()
    {
        return [
            DescriptionsTemplates::TYPE_DIAGNOSIS,
            DescriptionsTemplates::TYPE_RECOMMENDATION,
            DescriptionsTemplates::TYPE_TESTRESULT
        ];
    }

    /**
     * Поиск шаблонов
     *
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @return CommonList
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws \Throwable
     */
    public function listTemplates($page = 1, $limit = 10, $filter = [])
    {
        $this->validateFilter($filter);
        $fullTrimValidator = new FullTrimValidator();

        $query = DescriptionsTemplates::find()
            ->orderBy('caption');

        /*
         * Доступные организации + фильтр по ним
         */
        $organization_filter = !empty($filter['id_organization']) ? $filter['id_organization'] : false;
        $id_organizations = $this->getAllowedAndFilteredOrganizationsIds($organization_filter);

        /*
         *  Все публичные
         */
        if (!array_key_exists('public', $filter) || $filter['public'] == true) {

            $query->orWhere([
                'AND',
                ['public' => true],
                ['id_organization' => $id_organizations],
            ]);
        }

        /*
         *  Приватные в зависимости от фильтра организаций
         */
        if (!array_key_exists('public', $filter) || $filter['public'] == false) {

            if (empty($filter['id_organization'])) {
                $query->orWhere([
                    'public' => false,
                    'id_user' => $this->getCurrentUserId(),
                ]);
            } else {
                $query->orWhere([
                    'public' => false,
                    'id_user' => $this->getCurrentUserId(),
                    'id_organization' => $id_organizations,
                ]);
            }
        }

        /*
         * Заголовок
         */
        if (!empty($filter['caption'])) {
            $caption = $fullTrimValidator->validateValue($filter['caption']);
            $query->andWhere(['ILIKE', 'caption', $caption . '%', false]);
        }

        /*
         * Тип
         */
        if (!empty($filter['template_type'])) {
            $query->andWhere(['template_type' => $filter['template_type']]);
        }

        /*
         * Параметр отчета к которомму относиться данный комментарий
         */
        if (!empty($filter['param_tech_name'])) {
            $query->andWhere(['param_tech_name' => $filter['param_tech_name']]);
        }

        $query_count = clone $query;

        $query
            ->limit($limit)
            ->with('organization')
            ->with(['user' => function ($query) {
                /** @var $query yii\db\ActiveQuery * */
                $query->select([
                    "id",
                    "f_fio",
                    "i_fio",
                    "o_fio",
                    "fullname",
                ]);
            }])
            ->offset(($page - 1) * $limit);

        return new CommonList(
            'descriptions_templates',
            $query->asArray()->all(),
            $query_count->count(),
            $page,
            $limit
        );
    }

    /**
     * Возвращает отфильтрованный список доступных организаций
     *
     * @param bool|int[] $filter_ogs
     * @return array
     * @throws ForbiddenHttpException
     * @throws \Throwable
     */
    public function getAllowedAndFilteredOrganizationsIds($filter_ogs = false)
    {
        // Для всех - его организация
        $orgs_ids = [$this->getCurrentSpecialist()->id_organization];

        /*
         * «Системному администратор (гос)» доступны все «общие» шаблоны,
         * связанные организациями, входящими в организационную структуру пользователя
         */
        if (Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS)) {

            $id_organizations = (new UserOrgTreeRule())->organizationTreeIds(Yii::$app->user->getIdentity());

            if (!empty($id_organizations)) {
                $orgs_ids = array_merge($orgs_ids, $id_organizations);
            }
        }

        /*
         * «Администрации» доступны все «общие» шаблоны, связанные с организацией
         * пользователя и её дочерними организациями
         */
        if (Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS)) {

            $organizations = $this->getCurrentSpecialist()->getAllOrganizations();
            $id_organizations = ArrayHelper::getColumn($organizations, 'id');

            if (!empty($id_organizations)) {
                $orgs_ids = array_merge($orgs_ids, $id_organizations);
            }
        }

        // Только уникальные
        array_unique($orgs_ids);

        // Только те, которые указаны в массиве
        if (!empty($filter_ogs)) {
            $diff = array_diff($filter_ogs, $orgs_ids);
            if (!empty($diff)) {
                throw new ForbiddenHttpException('Недостаточно прав доступа: в фильтре выбраны организации, к которым у вас нет доступа');
            }
            $orgs_ids = array_intersect($orgs_ids, $filter_ogs);

        }

        return array_values($orgs_ids);
    }

    /**
     * Возвращает шаблон по его id
     *
     * @param $id
     * @return DescriptionsTemplates|null
     * @throws ForbiddenHttpException
     * @throws \Throwable
     */
    public function getTemplate($id)
    {
        $template = $this->findTemplateById($id, true);

        // Access
        $this->checkReadAccess($template);

        return $template;
    }

    /**
     * Создание шаблона
     *
     * @param $template
     * @param $public
     * @param $caption
     * @param $template_type
     * @param $param_tech_name
     * @return DescriptionsTemplates
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws \Throwable
     */
    public function create($template, $public, $caption, $template_type, $param_tech_name = NULL)
    {
        $desc_template = new DescriptionsTemplates();

        $desc_template->template = $template;
        $desc_template->public = $public;
        $desc_template->caption = $caption;
        $desc_template->id_organization = $this->getCurrentSpecialist()->id_organization;
        $desc_template->id_user = $this->getCurrentUserId();
        $desc_template->template_type = $template_type;
        $desc_template->param_tech_name = $param_tech_name;

        // Access
        $this->checkEditAccess($desc_template);

        if (!$desc_template->save()) {
            $errors = $desc_template->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при сохранении шаблона' : implode("\n", array_values($errors)));
        }

        return $desc_template;
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
     * @return DescriptionsTemplates|null
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws \Throwable
     */
    public function edit($id, $template, $public, $caption, $template_type, $param_tech_name = NULL)
    {
        $desc_template = $this->findTemplateById($id);

        // Access
        $this->checkEditAccess($desc_template);

        $desc_template->template = $template;
        $desc_template->public = $public;
        $desc_template->caption = $caption;
        $desc_template->template_type = $template_type;
        $desc_template->param_tech_name = $param_tech_name;

        /*
         * Пользователь мог отредактировать значимые поля - проверяем еще раз,
         * что ему это доступно
         */
        if ($desc_template->isAttributeChanged('public')) {
            $this->checkEditAccess($desc_template);
        }

        if (!$desc_template->save()) {
            $errors = $desc_template->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка при обновлении шаблона' : implode("\n", array_values($errors)));
        }

        return $desc_template;
    }

    /**
     * Удаление шаблона
     *
     * @param $id
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws \Throwable
     */
    public function delete($id)
    {
        $desc_template = $this->findTemplateById($id);

        // Access
        $this->checkEditAccess($desc_template);

        if ($desc_template->delete() === FALSE) {
            throw new BadRequestHttpException('Неизвестная ошибка при удалении шаблона');
        }
    }

    /**
     * Возвращает шаблон по его id
     * @param $id
     * @param $as_array
     * @return DescriptionsTemplates|array
     * @throws BadRequestHttpException
     */
    protected function findTemplateById($id, $as_array = false)
    {
        $template = DescriptionsTemplates::find()
            ->with('organization')
            ->with(['user' => function ($query) {
                /** @var $query yii\db\ActiveQuery * */
                $query->select([
                    "id",
                    "f_fio",
                    "i_fio",
                    "o_fio",
                    "fullname",
                ]);
            }])
            ->where(['id' => $id])
            ->asArray($as_array)
            ->one();

        if (empty($template)) {
            throw new BadRequestHttpException('Указанный шаблон не найден');
        }

        return $template;
    }

    /**
     * Проверка на доступность просмотра объекта
     * Проверка отдельно, тк зависит от ролей и "видимости" объекта
     *
     * @param DescriptionsTemplates $model
     * @throws ForbiddenHttpException
     * @throws \Throwable
     * @return boolean
     */
    protected function checkReadAccess($model)
    {
        /*
         * «Системному администратор (гос)» доступны все «общие» шаблоны,
         * связанные организациями, входящими в организационную структуру пользователя
         */
        if (Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS) && $model['public'] == true) {

            $check = (new UserOrgTreeRule())->execute(
                Yii::$app->user->getIdentity(), null, $model
            );

            if ($check) {
                return true;
            }
        }

        /*
         * «Администрации» доступны все «общие» шаблоны, связанные с организацией
         * пользователя и её дочерними организациями
         */
        if (Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS) && $model['public'] == true) {

            $check = (new UserAllOrgsRule())->execute(
                Yii::$app->user->getIdentity(), null, $model
            );

            if ($check) {
                return true;
            }
        }

        /*
         * «Ветеринарному специалисту» доступны личные шаблоны пользователя и все общие шаблоны,
         *  доступные организации пользователя
         */
        if (Yii::$app->user->can(Role::ROLE_VET_SPECIALIST_GOS) ||
            Yii::$app->user->can(Role::ROLE_VET_SPECIALIST_GOS_AMB) ||
            Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS) ||
            Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS)
        ) {

            $check = (
                ($model['id_user'] == $this->getCurrentUserId()) // Личные
                ||
                ($model['public'] === true && $model['id_organization'] == $this->getCurrentSpecialist()->id_organization)
            );

            if ($check) {
                return true;
            }
        }

        throw new ForbiddenHttpException('Недостаточно прав доступа');
    }

    /**
     * Проверка на доступность редактирования/удаления объекта
     * Проверка отдельно, тк зависит от ролей и "видимости" объекта
     *
     * @param $model
     * @throws ForbiddenHttpException
     * @throws \Throwable
     * @return boolean
     */
    protected function checkEditAccess($model)
    {

        /*
         * «Системному администратор (гос)» доступны все «общие» шаблоны, связанные организациями,
         * входящими в организационную структуру пользователя
         */
        if (Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS) && $model->public == true) {

            $check = (new UserOrgTreeRule())->execute(
                \Yii::$app->user->getIdentity(), null, $model
            );

            if ($check) {
                return true;
            }
        }

        /*
         * «Администрации» доступны все «общие» шаблоны, связанные с организацией пользователя
         * и её дочерними организациями
         */
        if (Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS) && $model->public == true) {

            $check = (new UserAllOrgsRule())->execute(
                \Yii::$app->user->getIdentity(), null, $model
            );

            if ($check) {
                return true;
            }
        }

        /*
         * «Ветеринарному специалисту» доступны только «личные» шаблоны
         *
         */
        if (Yii::$app->user->can(Role::ROLE_VET_SPECIALIST_GOS) ||
            Yii::$app->user->can(Role::ROLE_VET_SPECIALIST_GOS_AMB)
        ) {
            $check = ($model->id_user == $this->getCurrentUserId() && $model->public == false);

            if ($check) {
                return true;
            }
        }

        /*
         * Личные для админов
         */
        if (Yii::$app->user->can(Role::ROLE_SYSADMIN_GOS) ||
            Yii::$app->user->can(Role::ROLE_MANAGEMENT_GOS)){
            $check = ($model->id_user == $this->getCurrentUserId());

            if ($check) {
                return true;
            }
        }

        throw new ForbiddenHttpException('Недостаточно прав доступа');
    }

    /**
     * Проверка фильтра
     *
     * @param $filter
     * @throws BadRequestHttpException
     */
    protected function validateFilter($filter)
    {
        if (!is_array($filter)) {
            throw new BadRequestHttpException('Фильтр сформирован с ошибкой');
        }

        if (!empty($filter['id_organization']) && !is_array($filter['id_organization'])) {
            throw new BadRequestHttpException('Фильтр id_organizations должен быть массивом чисел');
        }

        if (!empty($filter['param_tech_name']) && !is_string($filter['param_tech_name'])) {
            throw new BadRequestHttpException('Фильтр param_tech_name должен быть строкой');
        }

        if (!empty($filter['template_type']) && !is_string($filter['template_type'])) {
            throw new BadRequestHttpException('Фильтр template_type должен быть строкой');
        }

        if (!empty($filter['template_type']) && is_string($filter['template_type']) &&
            !in_array($filter['template_type'], $this->getTypes())) {
            throw new BadRequestHttpException('Фильтр template_type содержит неизвестный тип');
        }

        if (!empty($filter['caption']) && !is_string($filter['caption'])) {
            throw new BadRequestHttpException('Фильтр caption должен быть строкой');
        }

        if (!empty($filter['public']) && !is_bool($filter['public'])) {
            throw new BadRequestHttpException('Фильтр public должен быть булевым значением');
        }
    }

    /**
     * Возвращает id текущего пользователя
     *
     * @return int|string
     * @throws \Throwable
     */
    protected function getCurrentUserId()
    {
        return Yii::$app->user->getIdentity()->getId();
    }

    /**
     * Возвращает текущего спеца
     * @return \app\models\db\Specialists
     * @throws ForbiddenHttpException
     * @throws \Throwable
     */
    protected function getCurrentSpecialist()
    {
        /** @var UserModel $user */
        $user = \Yii::$app->user->getIdentity();
        if (empty($user->specialist)) {
            throw new ForbiddenHttpException('Недостаточно прав доступа');
        }

        return $user->specialist;
    }

    /**
     * Возвращает список параметров "результаты исследования" с datatype text, которым можно назначить шаблоны
     * @return array
     */
    public function paramsList()
    {
        return (new Query)
            ->select(['id', 'tech_name', 'name'])
            ->from(Params::tableName())
            ->where(['datatype' => 'text'])
            ->andWhere(['ilike', 'name', new Expression('\'%результат%исследовани%\'')])
            ->orderBy(['name' => SORT_ASC])
            ->all();
    }
}
