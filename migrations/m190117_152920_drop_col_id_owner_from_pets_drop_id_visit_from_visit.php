<?php

use app\commands\migrate\Migration;

/**
 * Class m190117_152920_drop_col_id_owner_from_pets_drop_id_visit_from_visit
 */
class m190117_152920_drop_col_id_owner_from_pets_drop_id_visit_from_visit extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('pets', 'id_owner');
//        $this->dropForeignKey('fk-visits-id_visit','visits');
//        $this->dropColumn('visit', 'id_visit');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190117_152920_drop_col_id_owner_from_pets_drop_id_visit_from_visit cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190117_152920_drop_col_id_owner_from_pets_drop_id_visit_from_visit cannot be reverted.\n";

        return false;
    }
    */
}
