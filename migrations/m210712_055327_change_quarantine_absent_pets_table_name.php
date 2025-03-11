<?php

use app\commands\migrate\Migration;

/**
 * Class m210712_055327_change_quarantine_absent_pets_table_name
 */
class m210712_055327_change_quarantine_absent_pets_table_name extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE quarantine_absent_pets RENAME TO quarantine_detour_non_visit');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('ALTER TABLE quarantine_detour_non_visit RENAME TO quarantine_absent_pets');
    }
}
