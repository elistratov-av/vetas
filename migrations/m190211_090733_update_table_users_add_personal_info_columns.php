<?php

use app\commands\migrate\Migration;

/**
 * Class m190211_090733_update_table_users_add_personal_info_columns
 */
class m190211_090733_update_table_users_add_personal_info_columns extends Migration
{
    private $tableName = 'users';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%' . $this->tableName . '}}', 'f_fio', $this->string(150));  //->notNull()
        $this->addColumn('{{%' . $this->tableName . '}}', 'i_fio', $this->string(50));
        $this->addColumn('{{%' . $this->tableName . '}}', 'o_fio', $this->string(50));  //->notNull()
        $this->addColumn('{{%' . $this->tableName . '}}', 'fullname', $this->string());
        $this->addColumn('{{%' . $this->tableName . '}}', 'birthday', $this->date());
        $this->addColumn('{{%' . $this->tableName . '}}', 'sex', $this->string(1));  //->notNull()
        $this->addColumn('{{%' . $this->tableName . '}}', 'photo', $this->integer());

//        $this->createIndex(
//            'idx-' . $this->tableName . '-fio',
//            '{{%' . $this->tableName . '}}',
//            ['f_fio', 'i_fio', 'o_fio']
//        );

        // creates index for column `photo`
//        $this->createIndex(
//            'idx-' . $this->tableName . '-photo',
//            '{{%' . $this->tableName . '}}',
//            'photo'
//        );

        // add foreign key for table `files`
//        $this->addForeignKey(
//            'fk-' . $this->tableName . '-photo',
//            '{{%' . $this->tableName . '}}',
//            'photo',
//            'files',
//            'id',
//            'SET NULL'
//        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-' . $this->tableName . '-photo', '{{%' . $this->tableName . '}}');
        $this->dropIndex('idx-' . $this->tableName . '-photo', '{{%' . $this->tableName . '}}');
        $this->dropIndex('idx-' . $this->tableName . '-fio', '{{%' . $this->tableName . '}}');

        foreach (['f_fio', 'i_fio', 'o_fio', 'fullname', 'birthday', 'sex', 'photo'] as $column) {
            $this->dropColumn('{{%' . $this->tableName . '}}', $column);
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190211_090733_update_table_users_add_personal_info_columns cannot be reverted.\n";

        return false;
    }
    */
}
