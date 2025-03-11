<?php

use yii\db\Migration;

/**
 * Handles the creation of table `visit_service_report_serialservicenum`.
 */
class m190207_045450_create_visit_service_report_serialservicenum_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('visit_service_report_serialservicenum', [
            'id' => $this->primaryKey(),
            'year' => $this->integer(),
            'id_organization' => $this->integer(),
            'id_report' => $this->integer(),
            'last_number' => $this->integer(),
            'created_at' => $this->timestamp(0),
            'updated_at' => $this->timestamp(0),
        ]);

        $this->addCommentOnTable(
            'visit_service_report_serialservicenum', 'Последние значения порядкового номера исследования для отчетов'
        );

        foreach (['year', 'id_organization', 'id_report'] as $column) {
            $this->createIndex(
                'idx-visit_service_report_serialservicenum-' . $column,
                'visit_service_report_serialservicenum',
                $column
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('visit_service_report_serialservicenum');
    }
}
