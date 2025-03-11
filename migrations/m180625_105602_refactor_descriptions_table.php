<?php

use yii\db\Migration;

/**
 * Class m180625_105602_refactor_descriptions_table
 */
class m180625_105602_refactor_descriptions_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        //$this->dropIndex('descriptions_description_key', 'descriptions');
        //$this->dropIndex('descriptions_entity_id_key', 'descriptions');
        //$this->dropIndex('descriptions_entity_type_key', 'descriptions');

        $this->execute('ALTER TABLE descriptions DROP CONSTRAINT IF EXISTS descriptions_description_key');
        $this->execute('ALTER TABLE descriptions DROP CONSTRAINT IF EXISTS descriptions_entity_id_key');
        $this->execute('ALTER TABLE descriptions DROP CONSTRAINT IF EXISTS descriptions_entity_type_key');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180625_105602_refactor_descriptions_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180625_105602_refactor_descriptions_table cannot be reverted.\n";

        return false;
    }
    */
}
