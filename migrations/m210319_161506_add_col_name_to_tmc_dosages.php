<?php

use app\commands\migrate\Migration;

/**
 * Class m210319_161506_add_col_name_to_tmc_dosages
 */
class m210319_161506_add_col_name_to_tmc_dosages extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            'tmc.dosages',
            'name',
            $this->string(50)
        );
        $this->addCommentOnColumn(
            'tmc.dosages',
            'name',
            'Название для дозировки'
        );

        $sql = "UPDATE tmc.dosages SET name = '[Дозировка ' || tmc.dosages.id || ']' ";
        $this->execute($sql);

        $this->execute('ALTER TABLE tmc.dosages ALTER COLUMN name SET NOT NULL;');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('tmc.dosages', 'name');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210319_161506_add_col_name_to_tmc_dosages cannot be reverted.\n";

        return false;
    }
    */
}
