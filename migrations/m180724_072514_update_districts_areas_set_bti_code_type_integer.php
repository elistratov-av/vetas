<?php

use yii\db\Migration;

/**
 * Class m180724_072514_update_districts_areas_set_bti_code_type_integer
 */
class m180724_072514_update_districts_areas_set_bti_code_type_integer extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE "areas" ALTER COLUMN "bti_code" TYPE integer USING "bti_code"::integer');
        $this->execute('ALTER TABLE "districts" ALTER COLUMN "bti_code" TYPE integer USING "bti_code"::integer');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180724_072514_update_districts_areas_set_bti_code_type_integer cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180724_072514_update_districts_areas_set_bti_code_type_integer cannot be reverted.\n";

        return false;
    }
    */
}
