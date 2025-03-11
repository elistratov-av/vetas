<?php

use app\commands\migrate\Migration;

/**
 * Class m191120_014221_update_signed_visits_add_more_info
 */
class m191120_014221_update_signed_visits_add_more_info extends Migration
{
    private $tableName = 'signed_visits';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'cert_number', $this->string()->comment('Номер сертификата'));
        $this->addColumn($this->tableName, 'cert_owner', $this->string()->comment('Владелец сертификата'));
        $this->addColumn($this->tableName, 'valid_from', $this->dateTime(0)->comment('Действителен с'));
        $this->addColumn($this->tableName, 'valid_to', $this->dateTime(0)->comment('Действителен по'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        foreach (['cert_number', 'cert_owner', 'valid_from', 'valid_to'] as $column) {
            $this->dropColumn($this->tableName, $column);
        }
    }
}
