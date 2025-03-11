<?php

use app\commands\migrate\Migration;

/**
 * Class m191120_014950_update_visits_add_id_sign
 */
class m191120_014950_update_visits_add_id_sign extends Migration
{
    private $tableName = 'visits';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'id_sign', $this->integer()->comment('ID записи в таблице signed_visits'));

        $this->createIndex('idx_visits_id_sign', $this->tableName, 'id_sign');
        $this->addForeignKey('fk_visits_id_sign', $this->tableName, 'id_sign', 'signed_visits', 'id', 'SET NULL');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn($this->tableName, 'id_sign');
    }
}
