<?php

use yii\db\Migration;

/**
 * Class m180615_125338_drugs_view
 */
class m180615_125338_drugs_view extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('CREATE VIEW drugs_description_types AS SELECT * FROM descriptions');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180615_125338_drugs_view cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180615_125338_drugs_view cannot be reverted.\n";

        return false;
    }
    */
}
