<?php

use yii\db\Migration;

/**
 * Class m180625_124127_drugs_descriptions_view
 */
class m180625_124127_drugs_descriptions_view extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('CREATE VIEW drugs_descriptions AS SELECT * FROM descriptions');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180625_124127_drugs_descriptions_view cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180625_124127_drugs_descriptions_view cannot be reverted.\n";

        return false;
    }
    */
}
