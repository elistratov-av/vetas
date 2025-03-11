<?php

use yii\db\Migration;

/**
 * Class m180803_113608_create_rbac_data
 */
class m180803_113608_create_rbac_data extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->cleanup();

        // инициализируем RBAC-миграции (запуск через exec выбрасывает ошибку в тестовом окруженииб возможно дело в путях)
        // exec('php yii migrate --interactive=0 --migrationPath=@yii/rbac/migrations', $output);
        $parts = Yii::$app->createController('migrate/up');
        if (is_array($parts)) {
            /* @var $controller \yii\console\controllers\MigrateController */
            list($controller, $actionID) = $parts;
            $controller->migrationPath = '@yii/rbac/migrations';
            $controller->interactive = false;
            $oldController = Yii::$app->controller;
            Yii::$app->controller = $controller;
            $result = $controller->runAction($actionID);
            if ($oldController !== null) {
                Yii::$app->controller = $oldController;
            }
        }

        $auth = \Yii::$app->authManager;

        // Системный администратор
        $systemAdmin = $auth->createRole('systemAdmin');
        $systemAdmin->description = 'Системный администратор';
        $auth->add($systemAdmin);

        // Администрация
        $administrator = $auth->createRole('administrator');
        $administrator->description = 'Администрация';
        $auth->add($administrator);

        // Отдел кадров
        $personnelDepartment = $auth->createRole('personnelDepartment');
        $personnelDepartment->description = 'Отдел кадров';
        $auth->add($personnelDepartment);

        // Регистратура
        $registry = $auth->createRole('registry');
        $registry->description = 'Регистратура';
        $auth->add($registry);

        // Ветеринарный специалист
        $veterinarySpecialist = $auth->createRole('veterinarySpecialist');
        $veterinarySpecialist->description = 'Ветеринарный специалист';
        $auth->add($veterinarySpecialist);

        // Управление ролевой моделью доступа
        $rolesWrite = $auth->createPermission('rolesWrite');
        $rolesWrite->description = '[W] Управление ролевой моделью доступа';
        $auth->add($rolesWrite);
        $rolesRead = $auth->createPermission('rolesRead');
        $rolesRead->description = '[R] Управление ролевой моделью доступа';
        $auth->add($rolesRead);

        $auth->addChild($systemAdmin, $rolesWrite);
        $auth->addChild($systemAdmin, $rolesRead);
        $auth->addChild($administrator, $rolesRead);

        // Cправочник типов организаций
        $referenceOrgTypesWrite = $auth->createPermission('referenceOrgTypesWrite');
        $referenceOrgTypesWrite->description = '[W] Cправочник типов организаций';
        $auth->add($referenceOrgTypesWrite);
        $referenceOrgTypesRead = $auth->createPermission('referenceOrgTypesRead');
        $referenceOrgTypesRead->description = '[R] Cправочник типов организаций';
        $auth->add($referenceOrgTypesRead);

        $auth->addChild($systemAdmin, $referenceOrgTypesWrite);
        $auth->addChild($systemAdmin, $referenceOrgTypesRead);
        $auth->addChild($administrator, $referenceOrgTypesRead);

        // Справочник единиц измерения
        $referenceUnitMeasurementWrite = $auth->createPermission('referenceUnitMeasurementWrite');
        $referenceUnitMeasurementWrite->description = '[W] Справочник единиц измерения';
        $auth->add($referenceUnitMeasurementWrite);
        $referenceUnitMeasurementRead = $auth->createPermission('referenceUnitMeasurementRead');
        $referenceUnitMeasurementRead->description = '[R] Справочник единиц измерения';
        $auth->add($referenceUnitMeasurementRead);

        $auth->addChild($systemAdmin, $referenceUnitMeasurementWrite);
        $auth->addChild($systemAdmin, $referenceUnitMeasurementRead);
        $auth->addChild($administrator, $referenceUnitMeasurementRead);

        // Справочник причин снятия с регистрационного учета
        $referenceReasonsRemoveRegistrationWrite = $auth->createPermission('referenceReasonsRemoveRegistrationWrite');
        $referenceReasonsRemoveRegistrationWrite->description = '[W] Справочник причин снятия с регистрационного учета';
        $auth->add($referenceReasonsRemoveRegistrationWrite);
        $referenceReasonsRemoveRegistrationRead = $auth->createPermission('referenceReasonsRemoveRegistrationRead');
        $referenceReasonsRemoveRegistrationRead->description = '[R] Справочник причин снятия с регистрационного учета';
        $auth->add($referenceReasonsRemoveRegistrationRead);

        $auth->addChild($systemAdmin, $referenceReasonsRemoveRegistrationWrite);
        $auth->addChild($systemAdmin, $referenceReasonsRemoveRegistrationRead);
        $auth->addChild($administrator, $referenceReasonsRemoveRegistrationRead);

        // Справочник специализаций
        $referenceSpecializationsWrite = $auth->createPermission('referenceSpecializationsWrite');
        $referenceSpecializationsWrite->description = '[W] Справочник специализаций';
        $auth->add($referenceSpecializationsWrite);
        $referenceSpecializationsRead = $auth->createPermission('referenceSpecializationsRead');
        $referenceReasonsRemoveRegistrationRead->description = '[R] Справочник специализаций';
        $auth->add($referenceSpecializationsRead);

        $auth->addChild($systemAdmin, $referenceSpecializationsWrite);
        $auth->addChild($systemAdmin, $referenceSpecializationsRead);

        $auth->addChild($administrator, $referenceSpecializationsRead);

        $auth->addChild($personnelDepartment, $referenceSpecializationsWrite);
        $auth->addChild($personnelDepartment, $referenceSpecializationsRead);

        $auth->addChild($registry, $referenceSpecializationsRead);

        $auth->addChild($veterinarySpecialist, $referenceSpecializationsRead);

        // Справочник типов ТМЦ
        $referenceTypesTmcWrite = $auth->createPermission('referenceTypesTmcWrite');
        $referenceTypesTmcWrite->description = '[W] Справочник типов ТМЦ';
        $auth->add($referenceTypesTmcWrite);
        $referenceTypesTmcRead = $auth->createPermission('referenceTypesTmcRead');
        $referenceTypesTmcRead->description = '[R] Справочник типов ТМЦ';
        $auth->add($referenceTypesTmcRead);

        $auth->addChild($systemAdmin, $referenceTypesTmcWrite);
        $auth->addChild($systemAdmin, $referenceTypesTmcRead);

        $auth->addChild($administrator, $referenceTypesTmcRead);

        $auth->addChild($registry, $referenceTypesTmcRead);

        $auth->addChild($veterinarySpecialist, $referenceTypesTmcRead);

        // Справочник активных веществ
        $referenceActiveSubstancesWrite = $auth->createPermission('referenceActiveSubstancesWrite');
        $referenceActiveSubstancesWrite->description = '[W] Справочник активных веществ';
        $auth->add($referenceActiveSubstancesWrite);
        $referenceActiveSubstancesRead = $auth->createPermission('referenceActiveSubstancesRead');
        $referenceActiveSubstancesRead->description = '[R] Справочник активных веществ';
        $auth->add($referenceActiveSubstancesRead);

        $auth->addChild($systemAdmin, $referenceActiveSubstancesWrite);
        $auth->addChild($systemAdmin, $referenceActiveSubstancesRead);

        $auth->addChild($administrator, $referenceActiveSubstancesRead);

        $auth->addChild($registry, $referenceActiveSubstancesRead);

        $auth->addChild($veterinarySpecialist, $referenceActiveSubstancesRead);

        // ---
        $name = 'Справочник препаратов';
        $key = 'referencePreparations';
        $write = $auth->createPermission("{$key}Write");
        $write->description = "[W] {$name}";
        $auth->add($write);
        $read = $auth->createPermission("{$key}Read");
        $read->description = "[R] {$name}";
        $auth->add($read);

        $auth->addChild($systemAdmin, $write);
        $auth->addChild($systemAdmin, $read);

        $auth->addChild($administrator, $read);

        $auth->addChild($registry, $read);

        $auth->addChild($veterinarySpecialist, $read);

        // ---
        $name = 'Справочник вакцин';
        $key = 'referenceVaccines';
        $write = $auth->createPermission("{$key}Write");
        $write->description = "[W] {$name}";
        $auth->add($write);
        $read = $auth->createPermission("{$key}Read");
        $read->description = "[R] {$name}";
        $auth->add($read);

        $auth->addChild($systemAdmin, $write);
        $auth->addChild($systemAdmin, $read);

        $auth->addChild($administrator, $read);

        $auth->addChild($registry, $read);

        $auth->addChild($veterinarySpecialist, $read);

        // ---
        $name = 'Справочник оборудования';
        $key = 'referenceEquipment';
        $write = $auth->createPermission("{$key}Write");
        $write->description = "[W] {$name}";
        $auth->add($write);
        $read = $auth->createPermission("{$key}Read");
        $read->description = "[R] {$name}";
        $auth->add($read);

        $auth->addChild($systemAdmin, $write);
        $auth->addChild($systemAdmin, $read);

        $auth->addChild($administrator, $write);
        $auth->addChild($administrator, $read);

        $auth->addChild($registry, $read);

        $auth->addChild($veterinarySpecialist, $read);

        // ---
        $name = 'Справочник видов животных';
        $key = 'referenceSpecies';
        $write = $auth->createPermission("{$key}Write");
        $write->description = "[W] {$name}";
        $auth->add($write);
        $read = $auth->createPermission("{$key}Read");
        $read->description = "[R] {$name}";
        $auth->add($read);

        $auth->addChild($systemAdmin, $write);
        $auth->addChild($systemAdmin, $read);

        $auth->addChild($administrator, $read);

        $auth->addChild($registry, $read);

        $auth->addChild($veterinarySpecialist, $read);

        // ---
        $name = 'Справочник заболеваний';
        $key = 'referenceDiseases';
        $write = $auth->createPermission("{$key}Write");
        $write->description = "[W] {$name}";
        $auth->add($write);
        $read = $auth->createPermission("{$key}Read");
        $read->description = "[R] {$name}";
        $auth->add($read);

        $auth->addChild($systemAdmin, $write);
        $auth->addChild($systemAdmin, $read);

        $auth->addChild($administrator, $read);

        $auth->addChild($registry, $read);

        $auth->addChild($veterinarySpecialist, $read);

        // ---
        $name = 'Справочник АПН';
        $key = 'referenceAPN';
        $write = $auth->createPermission("{$key}Write");
        $write->description = "[W] {$name}";
        $auth->add($write);
        $read = $auth->createPermission("{$key}Read");
        $read->description = "[R] {$name}";
        $auth->add($read);

        $auth->addChild($systemAdmin, $write);
        $auth->addChild($systemAdmin, $read);

        $auth->addChild($administrator, $write);
        $auth->addChild($administrator, $read);

        $auth->addChild($registry, $read);

        $auth->addChild($veterinarySpecialist, $read);

        // ---
        $name = 'Прейскурант';
        $key = 'pricelist';
        $write = $auth->createPermission("{$key}Write");
        $write->description = "[W] {$name}";
        $auth->add($write);
        $read = $auth->createPermission("{$key}Read");
        $read->description = "[R] {$name}";
        $auth->add($read);

        $auth->addChild($systemAdmin, $write);
        $auth->addChild($systemAdmin, $read);

        $auth->addChild($administrator, $write);
        $auth->addChild($administrator, $read);

        $auth->addChild($registry, $read);

        $auth->addChild($veterinarySpecialist, $read);

        // ---
        $name = 'Справочная информация';
        $key = 'referenceInformation';
        $write = $auth->createPermission("{$key}Write");
        $write->description = "[W] {$name}";
        $auth->add($write);
        $read = $auth->createPermission("{$key}Read");
        $read->description = "[R] {$name}";
        $auth->add($read);

        $auth->addChild($systemAdmin, $write);
        $auth->addChild($systemAdmin, $read);

        $auth->addChild($administrator, $write);
        $auth->addChild($administrator, $read);

        $auth->addChild($personnelDepartment, $read);

        $auth->addChild($registry, $read);

        $auth->addChild($veterinarySpecialist, $read);

        // ---
        $name = 'Учет организаций';
        $key = 'accountingOrganizations';
        $write = $auth->createPermission("{$key}Write");
        $write->description = "[W] {$name}";
        $auth->add($write);
        $read = $auth->createPermission("{$key}Read");
        $read->description = "[R] {$name}";
        $auth->add($read);

        $auth->addChild($administrator, $write);
        $auth->addChild($administrator, $read);

        $auth->addChild($personnelDepartment, $read);

        $auth->addChild($registry, $read);

        $auth->addChild($veterinarySpecialist, $read);

        // ---
        $name = 'Баланс организаций';
        $key = 'balanceOrganizations';
        $write = $auth->createPermission("{$key}Write");
        $write->description = "[W] {$name}";
        $auth->add($write);
        $read = $auth->createPermission("{$key}Read");
        $read->description = "[R] {$name}";
        $auth->add($read);

        $auth->addChild($administrator, $write);
        $auth->addChild($administrator, $read);

        $auth->addChild($registry, $read);

        $auth->addChild($veterinarySpecialist, $read);

        // ---
        $name = 'Учет специалистов';
        $key = 'accountingSpecialists';
        $write = $auth->createPermission("{$key}Write");
        $write->description = "[W] {$name}";
        $auth->add($write);
        $read = $auth->createPermission("{$key}Read");
        $read->description = "[R] {$name}";
        $auth->add($read);

        $auth->addChild($administrator, $read);

        $auth->addChild($personnelDepartment, $write);
        $auth->addChild($personnelDepartment, $read);

        $auth->addChild($registry, $read);

        $auth->addChild($veterinarySpecialist, $read);

        // ---
        $name = 'Учет владельцев животных';
        $key = 'accountingPetOwners';
        $write = $auth->createPermission("{$key}Write");
        $write->description = "[W] {$name}";
        $auth->add($write);
        $read = $auth->createPermission("{$key}Read");
        $read->description = "[R] {$name}";
        $auth->add($read);

        $auth->addChild($administrator, $read);

        $auth->addChild($registry, $write);
        $auth->addChild($registry, $read);

        $auth->addChild($veterinarySpecialist, $write);
        $auth->addChild($veterinarySpecialist, $read);

        // ---
        $name = 'Учет животных';
        $key = 'accountingPets';
        $write = $auth->createPermission("{$key}Write");
        $write->description = "[W] {$name}";
        $auth->add($write);
        $read = $auth->createPermission("{$key}Read");
        $read->description = "[R] {$name}";
        $auth->add($read);

        $auth->addChild($administrator, $read);

        $auth->addChild($registry, $write);
        $auth->addChild($registry, $read);

        $auth->addChild($veterinarySpecialist, $write);
        $auth->addChild($veterinarySpecialist, $read);

        // ---
        $name = 'Управление рабочим графиком';
        $key = 'workScheduleManagement';
        $write = $auth->createPermission("{$key}Write");
        $write->description = "[W] {$name}";
        $auth->add($write);
        $read = $auth->createPermission("{$key}Read");
        $read->description = "[R] {$name}";
        $auth->add($read);

        $auth->addChild($administrator, $read);

        $auth->addChild($personnelDepartment, $write);
        $auth->addChild($personnelDepartment, $read);

        $auth->addChild($registry, $write);
        $auth->addChild($registry, $read);

        $auth->addChild($veterinarySpecialist, $read);

        // ---
        $name = 'Запись на прием';
        $key = 'manageReception';
        $write = $auth->createPermission("{$key}Write");
        $write->description = "[W] {$name}";
        $auth->add($write);
        $read = $auth->createPermission("{$key}Read");
        $read->description = "[R] {$name}";
        $auth->add($read);

        $auth->addChild($registry, $write);
        $auth->addChild($registry, $read);

        $auth->addChild($veterinarySpecialist, $write);
        $auth->addChild($veterinarySpecialist, $read);

        // ---
        $name = 'Оплата услуг';
        $key = 'paymentServices';
        $write = $auth->createPermission("{$key}Write");
        $write->description = "[W] {$name}";
        $auth->add($write);
        $read = $auth->createPermission("{$key}Read");
        $read->description = "[R] {$name}";
        $auth->add($read);

        $auth->addChild($registry, $write);
        $auth->addChild($registry, $read);

        $auth->addChild($veterinarySpecialist, $read);

        // ---
        $name = 'Проведение приема';
        $key = 'doReception';
        $write = $auth->createPermission("{$key}Write");
        $write->description = "[W] {$name}";
        $auth->add($write);
        $read = $auth->createPermission("{$key}Read");
        $read->description = "[R] {$name}";
        $auth->add($read);

        $auth->addChild($registry, $write);
        $auth->addChild($registry, $read);

        $auth->addChild($veterinarySpecialist, $read);
        $auth->addChild($veterinarySpecialist, $write);

        // ---
        $name = 'Карантин';
        $key = 'quarantine';
        $write = $auth->createPermission("{$key}Write");
        $write->description = "[W] {$name}";
        $auth->add($write);
        $read = $auth->createPermission("{$key}Read");
        $read->description = "[R] {$name}";
        $auth->add($read);

        $auth->addChild($administrator, $write);
        $auth->addChild($administrator, $read);

        $auth->addChild($registry, $read);

        $auth->addChild($veterinarySpecialist, $read);

        // ---
        $name = 'Отчет о нагрузке на ветеринарные учреждения и службы';
        $key = 'reportBurdenVeterinaryInstitutionsAndServices';
        $write = $auth->createPermission("{$key}Write");
        $write->description = "[W] {$name}";
        $auth->add($write);
        $read = $auth->createPermission("{$key}Read");
        $read->description = "[R] {$name}";
        $auth->add($read);

        $auth->addChild($administrator, $read);

        // ---
        $name = 'Отчет по контролю спроса';
        $key = 'reportDemandControl';
        $write = $auth->createPermission("{$key}Write");
        $write->description = "[W] {$name}";
        $auth->add($write);
        $read = $auth->createPermission("{$key}Read");
        $read->description = "[R] {$name}";
        $auth->add($read);

        $auth->addChild($administrator, $read);

        // ---
        $name = 'Отчет по охвату вакцинацией';
        $key = 'reportVaccinationCoverage';
        $write = $auth->createPermission("{$key}Write");
        $write->description = "[W] {$name}";
        $auth->add($write);
        $read = $auth->createPermission("{$key}Read");
        $read->description = "[R] {$name}";
        $auth->add($read);

        $auth->addChild($administrator, $read);

        // ---
        $name = 'Отчет по работе сотрудников';
        $key = 'reportEmployee';
        $write = $auth->createPermission("{$key}Write");
        $write->description = "[W] {$name}";
        $auth->add($write);
        $read = $auth->createPermission("{$key}Read");
        $read->description = "[R] {$name}";
        $auth->add($read);

        $auth->addChild($administrator, $read);

        // ---
        $name = 'Отчет по использованию препаратов';
        $key = 'reportUseDrugs';
        $write = $auth->createPermission("{$key}Write");
        $write->description = "[W] {$name}";
        $auth->add($write);
        $read = $auth->createPermission("{$key}Read");
        $read->description = "[R] {$name}";
        $auth->add($read);

        $auth->addChild($administrator, $read);

        // ---
        $name = 'Отчет по загрузке мощностей';
        $key = 'reportCapacityUse';
        $write = $auth->createPermission("{$key}Write");
        $write->description = "[W] {$name}";
        $auth->add($write);
        $read = $auth->createPermission("{$key}Read");
        $read->description = "[R] {$name}";
        $auth->add($read);

        $auth->addChild($administrator, $read);

        // ---
        $name = 'Отчет об административном делопроизводстве';
        $key = 'reportAdministrativeRecordKeeping';
        $write = $auth->createPermission("{$key}Write");
        $write->description = "[W] {$name}";
        $auth->add($write);
        $read = $auth->createPermission("{$key}Read");
        $read->description = "[R] {$name}";
        $auth->add($read);

        $auth->addChild($administrator, $read);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->cleanup();
    }

    private function cleanup()
    {
        $this->execute('DROP TABLE IF EXISTS auth_assignment');
        $this->execute('DROP TABLE IF EXISTS auth_item_child');
        $this->execute('DROP TABLE IF EXISTS auth_item');
        $this->execute('DROP TABLE IF EXISTS auth_rule');

        $this->execute('DELETE FROM migration WHERE version=\'m140506_102106_rbac_init\'');
        $this->execute('DELETE FROM migration WHERE version=\'m170907_052038_rbac_add_index_on_auth_assignment_user_id\'');
    }
}
