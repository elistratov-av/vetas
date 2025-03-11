<?php

use yii\db\Migration;

/**
 * Class m180813_114928_update_tmc_set_id_tmc_type_not_null
 */
class m180813_114928_update_tmc_set_id_tmc_type_not_null extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE tmc ALTER COLUMN id_tmc_type SET NOT NULL;');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180813_114928_update_tmc_set_id_tmc_type_not_null cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180813_114928_update_tmc_set_id_tmc_type_not_null cannot be reverted.\n";

        return false;
    }
    */
}
