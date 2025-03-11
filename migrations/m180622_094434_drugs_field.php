<?php

use yii\db\Migration;

/**
 * Class m180622_094434_drugs_field
 */
class m180622_094434_drugs_field extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE drugs ADD COLUMN IF NOT EXISTS id_active_substance INTEGER');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180622_094434_drugs_field cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180622_094434_drugs_field cannot be reverted.\n";

        return false;
    }
    */
}
