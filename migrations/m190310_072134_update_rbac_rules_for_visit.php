<?php

use app\commands\migrate\Migration;

/**
 * Class m190310_072134_update_rbac_rules_for_visit
 */
class m190310_072134_update_rbac_rules_for_visit extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = \Yii::$app->authManager;
        $rule = new \app\common\components\rbac\rules\VisitCompositeRule1();
        $auth->add($rule);

        $this->db->createCommand()->update(
            'auth_item',
            ['rule_name' => 'VisitCompositeRule1'],
            ['name' => 'activity.visits.edit']
        )
            ->execute();

        $this->db->createCommand()->update(
            'auth_item',
            ['rule_name' => 'UserOrgRule'],
            ['in', 'name', ['activity.visits.confirm-payment', 'activity.visits.start']]
        )
            ->execute();


        $this->db->createCommand()->update(
            'auth_item',
            ['rule_name' => 'VisitSpecialistRule'],
            ['name' => 'activity.visits.cancel']
        )
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->db->createCommand()->update(
            'auth_item',
            ['rule_name' => null],
            ['in', 'name', ['activity.visits.edit', 'activity.visits.confirm-payment', 'activity.visits.start', 'activity.visits.cancel']]
        )
            ->execute();

        $auth = \Yii::$app->authManager;
        $rule = new \app\common\components\rbac\rules\VisitCompositeRule1();
        $auth->remove($rule);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190310_072134_update_rbac_rules_for_visit cannot be reverted.\n";

        return false;
    }
    */
}
