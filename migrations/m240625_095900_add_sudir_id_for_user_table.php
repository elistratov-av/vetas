<?php

use app\commands\migrate\Migration;

/**
 * Class m240625_095900_add_sudir_id_for_user_table
 */
class m240625_095900_add_sudir_id_for_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->addColumn('public.users', 'sudir_uid', $this->string(255)->defaultValue(null));
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropColumn('public.users', 'sudir_uid');
    }
}
