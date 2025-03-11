<?php

use yii\db\Migration;

/**
 * Class m180823_105300_change_etp_message_table
 */
class m180823_105300_change_etp_message_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('etp.message', 'phone', $this->string());
        $this->addColumn('etp.message', 'last_name', $this->string());
        $this->addColumn('etp.message', 'first_name', $this->string());
        $this->addColumn('etp.message', 'middle_name', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('etp.message', 'phone');
        $this->dropColumn('etp.message', 'last_name');
        $this->dropColumn('etp.message', 'first_name');
        $this->dropColumn('etp.message', 'middle_name');
    }

}
