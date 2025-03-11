<?php

use app\commands\migrate\Migration;

/**
 * Class m180907_073128_update_breeds_set_name_description_type_text
 */
class m180907_073128_update_breeds_set_name_description_type_text extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('breeds', 'name', $this->text());
        $this->execute('ALTER TABLE breeds ALTER COLUMN name SET NOT NULL');
        $this->alterColumn('breeds', 'description', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180907_073128_update_breeds_set_name_description_type_text cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180907_073128_update_breeds_set_name_description_type_text cannot be reverted.\n";

        return false;
    }
    */
}
