<?php

use app\commands\migrate\Migration;

/**
 * Class m200917_105930_update_broods_table_add_id_owner_type
 */
class m200917_105930_update_broods_table_add_id_owner_type extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('public.broods', 'id_owner_type', $this->integer()->defaultValue(1));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200917_105930_update_broods_table_add_id_owner_type cannot be reverted.\n";

        return false;
    }
}
