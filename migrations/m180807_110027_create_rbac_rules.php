<?php

use yii\db\Migration;

/**
 * Class m180807_110027_create_rbac_rules
 */
class m180807_110027_create_rbac_rules extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        $rule = new app\common\components\rbac\RuleCRUD;
        $auth->add($rule);
        $rule = new app\common\components\rbac\RuleRU;
        $auth->add($rule);
        $rule = new app\common\components\rbac\RuleRUD;
        $auth->add($rule);

        // "Требования на 2018.07.23"

        $rule = new app\common\components\rbac\RuleCR;
        $auth->add($rule);

        // Справочник единиц измерения [W]
        $permissionNames = ['referenceUnitMeasurementWrite'];

        foreach ($permissionNames as $permissionName) {
            $permission = $auth->getPermission($permissionName);
            $permission->ruleName = $rule->name;
            $auth->update($permissionName, $permission);
        }

        $rule = new app\common\components\rbac\RuleCRU;
        $auth->add($rule);

        // Справочник типов ТМЦ [W]
        $permissionNames = ['referenceTmcTypesWrite'];

        foreach ($permissionNames as $permissionName) {
            $permission = $auth->getPermission($permissionName);
            $permission->ruleName = $rule->name;
            $auth->update($permissionName, $permission);
        }


        // Пользователь организации "Комитет ветеринарии города Москвы" (ID 445)

        $rule = new app\common\components\rbac\UserCommitteeRule;
        $auth->add($rule);

        // Справочник заболеваний [W]
        // Справочник АПН [W]
        // Справочная информация [W]

        $permissionNames = ['referenceDiseasesWrite', 'referenceAPNWrite', 'referenceInformationWrite'];

        foreach ($permissionNames as $permissionName) {
            $permission = $auth->getPermission($permissionName);
            $permission->ruleName = $rule->name;
            $auth->update($permissionName, $permission);
        }

        // Пользователь организации "Комитет ветеринарии города Москвы" (ID 445) +  "Требования на 2018.07.23"

        // Справочник специализаций [W]

        $rule = new app\common\components\rbac\UserCommitteeCRRule;
        $auth->add($rule);

        $permissionNames = ['referenceSpecializationsWrite'];

        foreach ($permissionNames as $permissionName) {
            $permission = $auth->getPermission($permissionName);
            $permission->ruleName = $rule->name;
            $auth->update($permissionName, $permission);
        }

        // Справочник оборудования [W]

        $rule = new app\common\components\rbac\UserCommitteeCRURule;
        $auth->add($rule);

        $permissionNames = ['referenceEquipmentWrite'];

        foreach ($permissionNames as $permissionName) {
            $permission = $auth->getPermission($permissionName);
            $permission->ruleName = $rule->name;
            $auth->update($permissionName, $permission);
        }


        // Редактирование доступно пользователям организации в части, связанной с организацией

        $rule = new app\common\components\rbac\UserOrgModelOrgRule;
        $auth->add($rule);

        $permissionNames = [
            'pricelistWrite',
            'balanceOrganizationsWrite',
            'accountingSpecialistsWrite',
            'workScheduleManagementWrite',
            'manageVisitsWrite',
            'paymentServicesWrite',
            'doVisitsWrite',
        ];

        foreach ($permissionNames as $permissionName) {
            $permission = $auth->getPermission($permissionName);
            $permission->ruleName = $rule->name;
            $auth->update($permissionName, $permission);
        }

        // Учет организаций

        $rule = new app\common\components\rbac\UserOrgModelOrgRURule;
        $auth->add($rule);
        $rule = new \app\common\components\rbac\AccountingOrganizationsRule();
        $auth->add($rule);
        $permissionName = 'accountingOrganizationsWrite';
        $permission = $auth->getPermission($permissionName);
        $permission->ruleName = $rule->name;
        $auth->update($permissionName, $permission);


        // Запись на прием в части ветеринарного специалиста (там дополнительное правило)

        $veterinarySpecialist = $auth->getRole('veterinarySpecialist');
        $permission = $auth->getPermission('manageVisitsWrite');
        $auth->removeChild($veterinarySpecialist, $permission);
        $permission = $auth->getPermission('manageVisitsRead');
        $auth->removeChild($veterinarySpecialist, $permission);

        $rule = new \app\common\components\rbac\VisitSpecialistRule();
        $auth->add($rule);

        $name = 'Запись на прием (ветеринарный специалист)';
        $key = 'manageVisitsVeterinarySpecialist';
        $write = $auth->createPermission("{$key}Write");
        $write->description = "[W] {$name}";
        $write->ruleName = $rule->name;
        $auth->add($write);

        $read = $auth->createPermission("{$key}Read");
        $read->description = "[R] {$name}";
        $auth->add($read);

        $auth->addChild($veterinarySpecialist, $write);
        $auth->addChild($veterinarySpecialist, $read);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }
}
