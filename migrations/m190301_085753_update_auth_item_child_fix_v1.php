<?php

use app\commands\migrate\Migration;

/**
 * Class m190301_085753_update_auth_item_child_fix_v1
 */
class m190301_085753_update_auth_item_child_fix_v1 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $items = [
            'activity.visits.manage' => 'activity.visits.manage.W',
            'admin.rbac.manage-users' => 'admin.rbac.manage-users.W',
            'admin.users.manage' => 'admin.users.manage.W',
            'data.classificators.active_substances' => 'data.classificators.active_substances.W',
            'data.classificators.breeds' => 'data.classificators.breeds.W',
            'data.classificators.diseases' => 'data.classificators.diseases.W',
            'data.classificators.drugs' => 'data.classificators.drugs.W',
            'data.classificators.equipments' => 'data.classificators.equipments.W',
            'data.classificators.exp_materials' => 'data.classificators.exp_materials.W',
            'data.classificators.measures' => 'data.classificators.measures.W',
            'data.classificators.org_types' => 'data.classificators.org_types.W',
            'data.classificators.reg_expire_reasons' => 'data.classificators.reg_expire_reasons.W',
            'data.classificators.specializations' => 'data.classificators.specializations.W',
            'data.classificators.species' => 'data.classificators.species.W',
            'data.classificators.tmc_types' => 'data.classificators.tmc_types.W',
            'data.classificators.vaccines' => 'data.classificators.vaccines.W',
            'data.organizations.balance' => 'data.organizations.balance.W',
            'data.organizations.manage' => 'data.organizations.manage.W',
            'data.owners.manage' => 'data.owners.manage.W',
            'data.pets.manage' => 'data.pets.manage.W',
            'data.pricelist.services' => 'data.pricelist.services.W',
            'data.specialists.manage' => 'data.specialists.manage.W',
        ];

        foreach ($items as $parent => $child) {
            \Yii::$app->db
                ->createCommand()
                ->update(
                    'auth_item_child',
                    [
                        'parent' => $child,
                        'child' => $parent,
                    ],
                    [
                        'parent' => $parent,
                    ]
                )->execute();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190301_085753_update_auth_item_child_fix_v1 cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190301_085753_update_auth_item_child_fix_v1 cannot be reverted.\n";

        return false;
    }
    */
}
