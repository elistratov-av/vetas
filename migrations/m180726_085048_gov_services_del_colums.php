<?php

use yii\db\Migration;

/**
 * Class m180726_085048_gov_services_del_colums
 */
class m180726_085048_gov_services_del_colums extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn("gov_services", "at_home");
        $this->dropColumn("gov_services", "flag_mos_ru");
        $this->dropColumn("gov_services", "id_tmc_type");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180726_085048_gov_services_del_colums cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180726_085048_gov_services_del_colums cannot be reverted.\n";

        return false;
    }
    */
}
