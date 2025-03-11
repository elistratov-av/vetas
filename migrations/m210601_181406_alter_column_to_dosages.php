<?php

use app\commands\migrate\Migration;

/**
 * Class m210601_181406_alter_column_to_dosages
 */
class m210601_181406_alter_column_to_dosages extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE tmc.dosages ALTER COLUMN dosage TYPE NUMERIC(11, 3);");
        $this->execute("ALTER TABLE tmc.dosages ALTER COLUMN dosage SET NOT NULL;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('tmc.dosages', 'dosage', $this->integer()->notNull());
    }
}
