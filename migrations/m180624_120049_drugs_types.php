<?php

use yii\db\Migration;

/**
 * Class m180624_120049_drugs_types
 */
class m180624_120049_drugs_types extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE "drugs"
ALTER COLUMN "unit" TYPE varchar(255)');
        $this->execute('ALTER TABLE drugs ADD COLUMN IF NOT EXISTS id_registered INTEGER');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180624_120049_drugs_types cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180624_120049_drugs_types cannot be reverted.\n";

        return false;
    }
    */
}
