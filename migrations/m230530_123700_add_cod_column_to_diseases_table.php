<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%gov_services}}`.
 */
class m230530_123700_add_cod_column_to_diseases_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('diseases', 'cod', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('diseases', 'cod');
    }
}
