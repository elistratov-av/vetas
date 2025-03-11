<?php

use yii\db\Migration;

/**
 * Handles the creation of table `reports`.
 */
class m180717_163416_create_reports_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('reports', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->unique()->notNull(),
            'report_type' => $this->char(1)->notNull()->defaultValue('R')->comment('R - Report, J - Journal, A - Act'),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->date(),
            'updated_at' => $this->date(),
        ]);

        $this->createIndex(
            'idx-reports-report_type',
            'reports',
            'report_type'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('reports');
    }
}
