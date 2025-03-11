<?php

use app\commands\migrate\Migration;
use app\common\components\rbac\Role;
use app\common\components\rbac\rules\VisitSpecialistRule;

/**
 * Class m190813_091206_2270_ambulance_sign_rbac
 */
class m190813_091206_2270_ambulance_sign_rbac extends \app\common\migrate\RbacMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = $this->getAuthManager();

        $permission = $auth->createPermission('ambulance.visits.sign');
        $permission->description = 'Управление приемом НВП: подпись приема (ЭЦП)';
        $permission->ruleName = (new VisitSpecialistRule())->name;
        $auth->add($permission);

        $role = $auth->getRole(Role::ROLE_VET_SPECIALIST_GOS_AMB);
        $auth->addChild($role, $permission);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190813_091206_2270_ambulance_sign_rbac cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190813_091206_2270_ambulance_sign_rbac cannot be reverted.\n";

        return false;
    }
    */
}
