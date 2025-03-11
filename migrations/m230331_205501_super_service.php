<?php

use app\commands\migrate\Migration;

/**
 * Class m230331_205501_super_service
 */
class m230331_205501_super_service extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {   
        $this->addColumn('visits', 'mosru_address', $this->string()->defaultValue(null));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('visits', 'mosru_address');
    }
}
