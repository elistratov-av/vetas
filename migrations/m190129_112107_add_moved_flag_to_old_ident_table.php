<?php

use app\commands\migrate\Migration;

/**
 * Class m190129_112107_add_moved_flag_to_old_ident_table
 */
class m190129_112107_add_moved_flag_to_old_ident_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('temp.pets_old_identification', 'moved', $this->boolean()->notNull()->defaultValue('false'));
        $this->addCommentOnColumn('temp.pets_old_identification','moved', 'Флаг для уже перенесенных записей в таблицу новых меток');

        $sql = "
        UPDATE temp.pets_old_identification as oldt SET moved = true
        WHERE oldt.id_ident_type = 1
        AND length(oldt.identification_code) = 15";

        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('temp.pets_old_identification','moved');
        //echo "m190129_112107_add_moved_flag_to_old_ident_table cannot be reverted.\n";

        //return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190129_112107_add_moved_flag_to_old_ident_table cannot be reverted.\n";

        return false;
    }
    */
}
