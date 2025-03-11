<?php

use yii\db\Migration;

/**
 * Class m180820_075738_add_column_color_to_shift_type_table
 */
class m180820_075738_add_column_color_to_shift_type_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('shift_type','color','character varying(255)');
        $sql = '
UPDATE 
      shift_type 
SET color=\'#1d8348\'
WHERE
    parent_id IS NULL;
';
        $this->execute($sql);

        $this->addCommentOnColumn('shift_type','color','Цвет (обязателен только для смен с "родительским" типом смены)');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180820_075738_add_column_color_to_shift_type_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180820_075738_add_column_color_to_shift_type_table cannot be reverted.\n";

        return false;
    }
    */
}
