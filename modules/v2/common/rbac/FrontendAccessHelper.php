<?php

namespace app\modules\v2\common\rbac;

use app\common\components\entity\EntityAccessChecker;
use app\common\components\entity\EntityAccessFilter;
use app\models\db\Quarantine;
use app\models\db\Shifts;
use app\models\db\Specialists;
use app\models\db\Timesheets;
use app\models\db\Visits;
use yii\base\InvalidCallException;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;

/**
 * Class FrontendAccessHelper
 *
 * @package app\modules\v2\common\rbac
 */
class FrontendAccessHelper
{
    use AccessTrait;

    /**
     * @param \app\common\models\UserModel $user
     *
     * @return array
     */
    public function prepareOutput($user)
    {
        if ($user->specialist === null) {
            throw new InvalidCallException('Метод следует вызывать для пользователя, которому назначен специалист');
        }

        return [
            'roles' => $this->prepareUserRolesInOrganization($user),
            'menu' => $this->menuAccess(),
            'all_roles' => $this->prepareUserRolesInAllOrganizations($user),
        ];
    }

    /**
     * @return array
     */
    private function menuAccess()
    {
        $app_user = \Yii::$app->user;
        $app_user_can_sys_admin_gos = $app_user
            ->can('sysAdminGos');
        $app_user_can_management_gos = $app_user
            ->can('managementGos');
        $app_user_can_management_priv_full = $app_user
            ->can('managementPrivFull');
        $app_user_can_management_priv_min = $app_user
            ->can('managementPrivMin');
        $user_is_admin = $app_user_can_sys_admin_gos
            || $app_user_can_management_gos
            || $app_user_can_management_priv_full
            || $app_user_can_management_priv_min;
        $user_can_pet_hotel = $app_user->can('data.pet-hotel.menu');
        return [
            // Баланс организации
            'balance' => $app_user->can('tmc.balance.list'),
            // Постановка ТМЦ на баланс (кнопки)
            'balance/add' => $app_user->can('tmc.balance.add'),
            // Поиск организаций
            'orgs' => $app_user->can('data.organizations.manage.menu'),
            // Сторонние организации
            'outside-organization' => $app_user->can('data.outside-organization.manage.menu'),
            //Управляющие организации
            'managing_organizations' => $app_user->can('data.managing_organizations.manage.menu'),
            //Приюты
            'shelters' => $app_user->can('data.shelters.manage.menu'),
            // Поиск животных
            'animals' => $app_user->can('data.pets.manage.menu'),
            // Владельцы животных
            'animals/owners' => $app_user->can('data.owners.manage.menu'),
            // Поиск специалистов
            'specialists' => $app_user->can('data.specialists.manage.menu'),
            // Приемы
            'visits' => $app_user->can('activity.visits.manage.menu'),
            // Приемы НВП
            'ambulance' => $app_user->can('ambulance.visits.manage.menu'),
            // Типы организаций
            'orgs/types' => $app_user->can('data.classificators.org_types.menu'),
            // Вольеры
            'aviary' => $app_user->can('data.classificators.aviary.menu'),
            // Свойства животных
            'animals/types' => $app_user->can('data.classificators.species-breeds.menu'),
            'animals/basis-of-disposal' => $app_user->can('data.classificators.basis-of-disposal.menu'),
            'animals/size' => $app_user->can('data.classificators.size.menu'),
            'animals/wool-type' => $app_user->can('data.classificators.wool-type.menu'),
            'animals/tail-type' => $app_user->can('data.classificators.tail-type.menu'),
            'animals/ear-type' => $app_user->can('data.classificators.ear-type.menu'),
            'animals/color' => $app_user->can('data.classificators.color.menu'),
            'animals/skill' => $app_user->can('data.classificators.skill.menu'),
            // Заболевания
            'diseases' => $app_user->can('data.classificators.diseases.menu'),
            // Заболевания по справочнику ГОСТ
            'gost-diseases' => $app_user->can('data.classificators.gost-diseases.menu'),
            // Причины снятия с регистрационного учета
            'deregistration' => $app_user->can('data.classificators.reg_expire_reasons.menu'),
            // Препараты
            'drugs' => $app_user->can('data.classificators.drugs.menu'),
            // Вакцины
            'vaccines' => $app_user->can('data.classificators.vaccines.menu'),
            // Активные вещества
            'active-substances' => $app_user->can('data.classificators.active_substances.menu'),
            // Оборудование
            'equipments' => $app_user->can('data.classificators.equipments.menu'),
            // Расходные материалы
            'exp-materials' => $app_user->can('data.classificators.exp_materials.menu'),
            // Специализации
            'specs' => $app_user->can('data.classificators.specializations.menu'),
            // Единицы измерения
            'units' => $app_user->can('data.classificators.measures.menu'),
            // Прейскурант
            'pricelist' => $app_user->can('data.pricelist.services.menu'),
            // Скидки
            'discount' => $app_user->can('data.pricelist.discount.menu'),
            // Шаблоны описаний
            'templates' => $app_user->can('data.descriptions-templates.manage.menu'),
            // Журналы
            'journal' => $app_user->can('data.journals.manage.menu'),
            'journal-new' => $app_user->can('data.journals.manage.menu'),
            //Справка
            'faq' => $app_user->can('data.faqs.manage.menu'),
            // Прививочные пункты
            'vacpoints' => $app_user->can('data.vacpoints.manage.menu'),
            // Госветнадзор
            'gosvetnadzor' => $app_user->can('data.gosvetnadzor.manage.menu'),
            // Административно-правовые нарушения
            'violation-admin-rights' => $app_user->can('data.gosvetnadzor.violation_admin_rights.menu'),
            // Карантин
            'quarantine' => $app_user->can('quarantine.manage.menu'),
            // Справка
            'help' => $app_user->can('data.help.manage.menu'),
            // Животные приюта
            'read-only-user' => $app_user->can('data.ro'),
            'outside-orgs' => !$app_user->can('data.ro'),
            //'shelter' => $app_user->can('shelter.manage.menu'),
            // Калькулятор доз препарата
            'dosages/list' => $app_user->can('data.dosages.tab.menu'),
            'dosages/create' => $app_user->can('data.dosages.add.menu'),
            'dosages/edit' => $app_user->can('data.dosages.add.menu'),
            'dosages/calculate' => $app_user->can('data.dosages.calculate.menu'),
            // Настройка фом выпуска
            'production-form/list' => $app_user->can('data.production_form.tab.menu'),
            'production-form/create' => $app_user->can('data.production_form.edit.menu'),
            'production-form/edit' => $app_user->can('data.production_form.edit.menu'),

            // Категории ТМЦ
            'categories' => $app_user->can('data.classificators.categories.menu'),
            // Категории ТМЦ - услуги
            'categories-gov-services' => $app_user->can('data.pricelist.categories.menu'),

            // Сервис "Поиск животных"
            'ads' => $app_user->can('found_pet.manage.menu'),

            // Прививочные пункты
            'vaccination-stations' => $app_user->can('data.vaccinationStation.menu'),

            // Упрощенные вакцинации
            'vaccination-journals/stations' => $app_user->can('activity.vaccinationJournal.menu'),
            'vaccination-journals/flats' => $app_user->can('activity.vaccinationJournal.menu'),
            'vaccination-journals/shelters' => $app_user->can('activity.vaccinationJournal.menu'),

            // Контатный-центр
            'callcenter' => $app_user->can('activity.callcenter.menu'),

            // Аналитика
            'analytics' => $app_user->can('activity.analytics.menu'),

            //Инструкции для пользователей
            'instructions-for-users' => $app_user->can('instructions_for_users.menu'),

            // Типы смен
            'shift-type-ref/shift-type-ref/create' => $app_user->can('data.shift_type_ref.admin'),
            'shift-type-ref/shift-type-ref/delete' => $app_user->can('data.shift_type_ref.admin'),
            'shift-type-ref/shift-type-ref' => $app_user->can('data.shift_type_ref.user'),

            // Типы смен
            'shift-type-ref/invalid-intersections/create' => $app_user->can('data.shift_type_ref.admin'),
            'shift-type-ref/invalid-intersections' => $app_user->can('data.shift_type_ref.user'),

            // Администрирование
            'administration/user' => $app_user_can_sys_admin_gos,
            'administration/role' => $app_user_can_sys_admin_gos,
            'administration/personal-cabinet' => $app_user_can_sys_admin_gos,

            // Учет ТМЦ
            // TODO:use-rbac-storage
            'tmc/menu' =>
                $app_user->can('vetSpecGosAmb')
                || $app_user->can('managementGos')
                || $app_user->can('vetSpecGos')
                || $app_user_can_sys_admin_gos
                || $app_user->can('technicMto'),

            'pet-hotel' => $user_can_pet_hotel,
            'pet-hotel/status/all' => $user_can_pet_hotel,
            'administration/faq2' => $user_is_admin,
            'administration/newsletter' => $user_is_admin,
            'administration/personal-cabinet' =>
                $app_user->can('vetSpecGosAmb')
                || $app_user->can('managementGos')
                || $app_user->can('vetSpecGos')
                || $app_user_can_sys_admin_gos
                || $app_user->can('technicMto')
                ||$app_user->can('shelter.manage.menu'),
            'faq2' => !$user_is_admin,
        ];
    }

    /**
     * @param array $urls
     *
     * @return array
     */
    public function checkUrlAccess($urls)
    {
        $results = [];

        foreach ($urls as $url) {
            $result = true;
            $model = null;
            $params = [];
            if (strpos($url, 'v2/', 0) === 0) {
                $pattern = '#^(v2\/[a-z-]+\/[a-z-]+\/[a-z-]+)(\/*)(\d*)$#m';
                preg_match($pattern, $url, $matches);
                if (!empty($matches[1])) {
                    $actionUniqueId = $matches[1];
                    if (!empty($matches[3])) {
                        $modelId = $matches[3];
                        $model = $this->findModelV2($actionUniqueId, $modelId);
                    }
                    if ($model === null) {
                        $this->fixIdOrganization($params);
                    }
                    $result = $this->checkAccess($actionUniqueId, $model, $params, false);
                }
            } elseif (strpos($url, 'v1/', 0) === 0) {
                $pattern = '#^v1\/([a-z-]+)\/([a-z-]+)(\/*)(\d*)$#m';
                preg_match($pattern, $url, $matches);
                if (!empty($matches[1]) && !empty($matches[2])) {
                    $entity = $matches[1];
                    $actionId = $matches[2];
                    if (!empty($matches[4])) {
                        $modelId = $matches[4];
                        $model = $this->findModelV1($entity, $modelId);
                    }
                    /* @var $checker \app\common\components\entity\EntityAccessChecker */
                    $checker = new EntityAccessChecker();
                    if ($model !== null) {
                        $params['model'] = $model;
                    } else {
                        $this->fixIdOrganization($params);
                    }
                    $result = $checker->checkAccess($entity, $actionId, $params, (new EntityAccessFilter()));
                }
            }
            $results[$url] = $result;
        }

        return $results;
    }

    /**
     * @param \app\common\models\UserModel $user
     *
     * @return array
     */
    public function prepareUserRolesInOrganization($user)
    {
        if ($user->specialist === null) {
            return [];
        }
        if ($user->specialist->isExpelledAtDate()) {
            return [];
        }

        return $this->findAllUserRolesInOrganization($user->id, $user->id_specialist);
    }

    /**
     * @param \app\common\models\UserModel $user
     *
     * @return array
     */
    public function prepareUserRolesInAllOrganizations($user)
    {
        $result = [];

        foreach ($user->specialists as $specialist) {
            if ($specialist->isExpelledAtDate()) {
                continue;
            }
            $roles = $this->findAllUserRolesInOrganization($user->id, $specialist->id);
            if (!empty($roles)) {
                $result[] = [
                    'id_organization' => $specialist->id_organization,
                    'roles' => $roles,
                ];
            }
        }

        return $result;
    }

    /**
     * @param int $id_user
     * @param int $id_specialist
     *
     * @return array
     */
    private function findAllUserRolesInOrganization($id_user, $id_specialist)
    {
        $roles = [];

        /* @var $auth \app\common\components\rbac\DbManager */
        $auth = \Yii::$app->authManager;
        $rbacData = $auth->getRolesByUser($id_user, $id_specialist);
        /* @var $rbacData \yii\rbac\Role[] */
        foreach ($rbacData as $data) {
            $roles[] = [
                'name' => $data->name,
                'description' => empty($data->description) ? '' : mb_substr($data->description, 0,
                    (int)mb_strrpos($data->description, "\n")),
            ];
        }

        return $roles;
    }

    /**
     * фикс для правил с id_organization
     *
     * @param array $params
     */
    private function fixIdOrganization(&$params)
    {
        /* @var $user \app\common\models\UserModel */
        $user = \Yii::$app->user->getIdentity();
        if (!empty($user->specialist)) {
            $params['id_organization'] = $user->specialist->id_organization;
        }
    }

    /**
     * @param string $entity
     * @param int    $modelId
     *
     * @return \yii\db\ActiveRecord|null
     */
    private function findModelV1($entity, $modelId)
    {
        $className = 'app\\models\\db\\' . Inflector::id2camel(str_replace('_', '-', $entity));

        return $this->findModel($className, $modelId);
    }

    /**
     * @param string $actionUniqueId
     * @param int    $modelId
     *
     * @return \yii\db\ActiveRecord|null
     */
    private function findModelV2($actionUniqueId, $modelId)
    {
        $className = ArrayHelper::getValue(self::v2ActionClassMap(), $actionUniqueId);

        return empty($className) ? null : $this->findModel($className, $modelId);
    }

    /**
     * @param string $className
     * @param int    $modelId
     *
     * @return \yii\db\ActiveRecord|null
     */
    private function findModel($className, $modelId)
    {
        try {
            $model = call_user_func([$className, 'findOne'], ['id' => $modelId]);
        } catch (\Throwable $e) {
            $model = null;
        }

        return $model;
    }

    /**
     * @return array
     */
    public static function v2ActionClassMap()
    {
        return [
            'v2/visit/visit/edit' => Visits::class,
            'v2/visit/visit/start' => Visits::class,
            'v2/visit/visit/finish' => Visits::class,
            'v2/visit/visit/cancel' => Visits::class,
            'v2/visit/visit/confirm-payment' => Visits::class,
            'v2/visit/visit/sign' => Visits::class,
            'v2/visit/visit/edit-preferences' => Visits::class,
            'v2/visit/ambulance/sign' => Visits::class,
            'v2/visit/ambulance/edit-preferences' => Visits::class,
            'v2/visit/services/add' => Visits::class,
            'v2/visit/services/edit' => Visits::class,
            'v2/visit/services/delete' => Visits::class,
            'v2/visit/descriptions/save' => Visits::class,
            'v2/visit/descriptions/file' => Visits::class,
            'v2/visit/service-tmcs/save' => Visits::class,
            'v2/visit/service-tmcs/save-equipments' => Visits::class,
            'v2/specialist/specialist/edit' => Specialists::class,
            'v2/specialist/specialist/delete' => Specialists::class,
            'v2/timesheet/shift/save' => Shifts::class,
            'v2/timesheet/shift/delete' => Shifts::class,
            'v2/timesheet/timesheet/save' => Timesheets::class,
            'v2/quarantine/quarantines/notify' => Quarantine::class,
            'v2/visit/visit/notify' => Visits::class,
            'v2/reports/notification/check' => Visits::class,
            'v2/reports/notification/send' => Visits::class,
            'v2/visit/violation/report' => Visits::class,
        ];
    }
}
