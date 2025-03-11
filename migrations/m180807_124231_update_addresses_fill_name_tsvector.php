<?php

use yii\db\Migration;

/**
 * Class m180807_124231_update_addresses_fill_name_tsvector
 */
class m180807_124231_update_addresses_fill_name_tsvector extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("UPDATE addresses SET name_tsv = to_tsvector('russian', name)");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }
}
