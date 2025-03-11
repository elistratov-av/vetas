<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%disease}}`.
 */
class m230925_114856_add_gost_code_column_to_disease_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('diseases', 'gost_code', $this->string()->defaultValue(null)->comment('Код ГОСТ для болезни'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('diseases', 'gost_code');
    }
}
