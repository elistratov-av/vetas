<?php

namespace app\common\components\rbac;

/**
 * Class Role
 * @package app\common\components\rbac
 */
class Role extends \yii\rbac\Role
{
    /**
     * Оператор контактного центра
     */
    const ROLE_CALLCENTER_OPERATOR = 'callCenterOperator';
    /**
     * Системный администратор (гос)
     */
    const ROLE_SYSADMIN_GOS = 'sysAdminGos';
    /**
     * Администрация (гос)
     */
    const ROLE_MANAGEMENT_GOS = 'managementGos';
    /**
     * Регистратура (гос)
     */
    const ROLE_REGISTRY_GOS = 'registryGos';
    /**
     * Ветеринарный специалист (гос)
     */
    const ROLE_VET_SPECIALIST_GOS = 'vetSpecGos';
    /**
     * Ветеринарный специалист выездной бригады (гос)
     */
    const ROLE_VET_SPECIALIST_GOS_AMB = 'vetSpecGosAmb';
    /**
     * Диспетчер
     */
    const ROLE_DISPATCHER = 'dispatcher';
    /**
     * Инспектор
     */
    const ROLE_INSPECTOR = 'inspector';
    /**
     * Инспектор просмотр
     */
    const ROLE_INSPECTOR_READONLY = 'inspectorReadonly';
    /**
     * Администрация приюта
     */
    const ROLE_SHELTER_MANAGEMENT = 'managementShelter';
    /**
     * Специалист приюта
     */
    const ROLE_SHELTER_SPECIALIST = 'specShelter';
    /**
     * Администратор деятельности приютов
     */
    const ROLE_SHELTER_ACTIVITY_ADMIN = 'shelterActivityAdmin';
    /**
     * Системный администратор приюта
     */
    const ROLE_SHELTER_SYS_ADMIN = 'shelterSysAdmin';
    /**
     * Приюты (просмотр)
     */
    const ROLE_SHELTER_VIEWER = 'shelterViewer';
    /**
     * Ветеринарный врач приюта
     */
    const ROLE_SHELTER_VETERINARIAN = 'shelterVeterinarian';
    /**
     * Специалист по мониторингу и учету городской фауны
     */
    const ROLE_SHELTER_FAUNA_MONITORING_SPEC = 'shelterFaunaMonitoringSpecialist';
    /**
     * Специалист по социализации животных приюта
     */
    const ROLE_SHELTER_ANIMAL_SOCIALIZATION_SPEC = 'shelterAnimalSocializationSpecialist';
    /**
     * Модератор сервиса "Поиск животных"
     */
    const ROLE_FOUND_PET_MODERATOR = 'foundPetModerator';

    const ROLES_SHELTER = [
        self::ROLE_SHELTER_VIEWER => self::ROLE_SHELTER_VIEWER,
        self::ROLE_SHELTER_MANAGEMENT => self::ROLE_SHELTER_MANAGEMENT,
        self::ROLE_SHELTER_SYS_ADMIN => self::ROLE_SHELTER_SYS_ADMIN,
        self::ROLE_SHELTER_SPECIALIST => self::ROLE_SHELTER_SPECIALIST,
        self::ROLE_SHELTER_ACTIVITY_ADMIN => self::ROLE_SHELTER_ACTIVITY_ADMIN,
        self::ROLE_SHELTER_VETERINARIAN => self::ROLE_SHELTER_VETERINARIAN,
        self::ROLE_SHELTER_FAUNA_MONITORING_SPEC => self::ROLE_SHELTER_FAUNA_MONITORING_SPEC,
        self::ROLE_SHELTER_ANIMAL_SOCIALIZATION_SPEC => self::ROLE_SHELTER_ANIMAL_SOCIALIZATION_SPEC,
    ];

    /**
     * Техник МТО
     */
    const ROLE_TECHNIC_MTO = 'technicMto';

    const ROLE_SYSADMIN_PRIVATE_FULL = 'sysAdminPrivFull';
    const ROLE_MANAGEMENT_PRIVATE_FULL = 'managementPrivFull';
    const ROLE_MANAGEMENT_PRIVATE_MIN = 'managementPrivMin';
    const ROLE_REGISTRY_PRIVATE_FULL = 'registryPrivFull';
    const ROLE_REGISTRY_PRIVATE_MIN = 'registryPrivMin';
    const ROLE_VET_SPECIALIST_PRIVATE_FULL = 'vetSpecPrivFull';
    const ROLE_VET_SPECIALIST_PRIVATE_MIN = 'vetSpecPrivMin';

    /**
     * @return array
     */
    public static function gosOrgRoles()
    {
        return array_merge([
            self::ROLE_SYSADMIN_GOS,
            self::ROLE_MANAGEMENT_GOS,
            self::ROLE_REGISTRY_GOS,
            self::ROLE_VET_SPECIALIST_GOS,
            self::ROLE_VET_SPECIALIST_GOS_AMB,
            self::ROLE_DISPATCHER,
            self::ROLE_INSPECTOR,
            self::ROLE_INSPECTOR_READONLY,
            self::ROLE_SHELTER_MANAGEMENT,
            self::ROLE_SHELTER_SPECIALIST,
            self::ROLE_FOUND_PET_MODERATOR,
            self::ROLE_TECHNIC_MTO,
            self::ROLE_CALLCENTER_OPERATOR,
        ], array_keys(self::ROLES_SHELTER));
    }

    /**
     * @return array
     */
    public static function privateOrgRoles()
    {
        return [
            self::ROLE_SYSADMIN_PRIVATE_FULL,
            self::ROLE_MANAGEMENT_PRIVATE_FULL,
            self::ROLE_MANAGEMENT_PRIVATE_MIN,
            self::ROLE_REGISTRY_PRIVATE_FULL,
            self::ROLE_REGISTRY_PRIVATE_MIN,
            self::ROLE_VET_SPECIALIST_PRIVATE_FULL,
            self::ROLE_VET_SPECIALIST_PRIVATE_MIN,
        ];
    }

    /**
     * @return array
     */
    public static function shelterRoles()
    {
        return [
            self::ROLE_SHELTER_MANAGEMENT,
            self::ROLE_SHELTER_SPECIALIST,
        ];
    }

    /**
     * @param string $roleName
     * @param \app\common\components\rbac\DbManager $auth
     * @return string
     */
    public static function humanName($roleName, $auth = null)
    {
        $auth = $auth ?? \Yii::$app->authManager;
        $role = $auth->getRole($roleName);
        if ($role === null) {
            return '';
        }

        $rpos = mb_strrpos($role->description, "\n");

        return empty($role->description)
            ? $roleName
            : ($rpos === false ? $role->description : mb_substr($role->description, 0, $rpos));
    }
}
