<?php

use yii\db\Migration;

/**
 * Class m180724_073213_refactor_description_types_table
 */
class m180724_073213_refactor_description_types_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE public.description_types DROP CONSTRAINT description_types_name_key');
        $this->createIndex('description_types_comosite', 'description_types', ['name', 'entity_type'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('description_types_comosite', 'description_types');
        $this->createIndex('description_types_name_key', 'description_types', 'name', true);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180724_073213_refactor_description_types_table cannot be reverted.\n";

        return false;
    }
    */
}
