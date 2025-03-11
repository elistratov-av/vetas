<?php

use app\commands\migrate\Migration;

/**
 * Class m180906_133645_update_specialists_set_id_organization_not_null
 */
class m180906_133645_update_specialists_set_id_organization_not_null extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE specialists ALTER COLUMN id_organization SET NOT NULL');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180906_133645_update_specialists_set_id_organization_not_null cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180906_133645_update_specialists_set_id_organization_not_null cannot be reverted.\n";

        return false;
    }
    */
}
