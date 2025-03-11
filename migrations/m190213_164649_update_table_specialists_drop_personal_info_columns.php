<?php

use app\commands\migrate\Migration;

/**
 * Class m190213_164649_update_table_specialists_drop_personal_info_columns
 */
class m190213_164649_update_table_specialists_drop_personal_info_columns extends Migration
{
    private $tableName = 'specialists';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('DROP TRIGGER IF EXISTS specialist_fullname_trg ON specialists;');
        $this->execute('DROP FUNCTION IF EXISTS specialist_fullname_func;');

        $this->dropIndex('idx-fio', '{{%' . $this->tableName . '}}');
        $this->dropForeignKey('fk-specialists-photo', '{{%' . $this->tableName . '}}');
        $this->dropIndex('idx-specialists-photo', '{{%' . $this->tableName . '}}');

        $columns = [
            'fullname',
            'f_fio',
            'i_fio',
            'o_fio',
            'birthday',
            'sex',
            'photo',
        ];

        foreach ($columns as $column) {
            $this->dropColumn('{{%' . $this->tableName . '}}', $column);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190213_164649_update_table_specialists_drop_personal_info_columns cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190213_164649_update_table_specialists_drop_personal_info_columns cannot be reverted.\n";

        return false;
    }
    */
}
