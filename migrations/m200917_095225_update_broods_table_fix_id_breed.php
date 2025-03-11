<?php

use app\commands\migrate\Migration;

/**
 * Class m200917_095225_update_broods_table_fix_id_breed
 */
class m200917_095225_update_broods_table_fix_id_breed extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('public.broods', 'id_breeds', 'id_breed');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200917_095225_update_broods_table_fix_id_breed cannot be reverted.\n";

        return false;
    }
}
