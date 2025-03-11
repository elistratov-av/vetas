<?php

use app\commands\migrate\Migration;

/**
 * Class m180921_082046_delete_column_count_flag_from_service_measures
 */
class m180921_082046_delete_column_count_flag_from_service_measures extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("COMMENT ON COLUMN service_types.count_flag IS NULL;");
        $this->execute('ALTER TABLE service_types DROP COLUMN IF EXISTS count_flag;');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180921_082046_delete_column_count_flag_from_service_measures cannot be reverted.\n";

        return false;
    }
}
