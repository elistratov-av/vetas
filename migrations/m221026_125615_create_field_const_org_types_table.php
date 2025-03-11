<?php

use yii\db\Migration;

/**
 * Handles the creation of table `field_const_org_types`.
 */
class m221026_125615_create_field_const_org_types_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('org_types', 'const', $this->string(100));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('org_types', 'const');
    }
}
