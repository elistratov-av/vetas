<?php

use app\commands\migrate\Migration;

/**
 * Class m181101_063515_update_reports_table_add_field_grouped
 */
class m181101_063515_update_reports_table_add_field_grouped extends Migration
{
    private $tableName = 'public.reports';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%' . $this->tableName . '}}', 'grouped', $this->boolean()->notNull()->defaultValue(false));

        $reports = [
            'Результат биохимического исследования крови',
            'Результат гормональных исследований крови',
            'Результат общего клинического анализа крови',
        ];

        foreach ($reports as $name) {
            $this->update('{{%' . $this->tableName . '}}', ['grouped' => true], ['name' => $name, 'report_type' => 'R']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%' . $this->tableName . '}}', 'grouped');
    }
}
