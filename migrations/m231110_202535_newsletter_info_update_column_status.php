<?php

use app\commands\migrate\Migration;

class m231110_202535_newsletter_info_update_column_status extends Migration
{
    /**
     * @throws \yii\db\Exception
     */
    public function safeUp()
    {
        $this->db->createCommand()
            ->update('newsletter_info', ['status' => 2], ['status' => 3])
            ->execute();
        return true;
    }

    public function safeDown()
    {
        // не отменяется
        return false;
    }
}
