<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%gov_services}}`.
 */
class m230526_154300_add_id_service_mosru_column_to_gov_services_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('gov_services', 'id_service_mosru', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('gov_services', 'id_service_mosru');
    }
}
