<?php

use app\commands\migrate\Migration;

/**
 * Class m201222_081325_alter_table_etp_message_v2
 */
class m201222_081325_alter_table_etp_message_v2 extends Migration
{
    private static $table = 'etp.message_v2';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(self::$table, 'system_id', $this->string());
        $this->addColumn(self::$table, 'message_id', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(self::$table, 'system_id');
        $this->dropColumn(self::$table, 'message_id');
    }
}
