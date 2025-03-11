<?php

use app\commands\migrate\Migration;

/**
 * Class m181207_135224_drop_default_null_from_specialists_birthdate
 */
class m181207_135224_drop_default_null_from_specialists_birthdate extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE specialists ALTER COLUMN birthday DROP NOT NULL");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("ALTER TABLE specialists ALTER COLUMN birthday SET NOT NULL");
    }
}
