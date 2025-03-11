<?php

use yii\db\Migration;

/**
 * Class m180628_105603_spec_view
 */
class m180628_105603_spec_view extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('CREATE VIEW specialists_specializations AS SELECT * FROM personal_specializations');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180628_105603_spec_view cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180628_105603_spec_view cannot be reverted.\n";

        return false;
    }
    */
}
