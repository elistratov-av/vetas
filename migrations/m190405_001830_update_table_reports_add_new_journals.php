<?php

use app\commands\migrate\Migration;
use yii\db\Query;

/**
 * Class m190405_001830_update_table_reports_add_new_journals
 */
class m190405_001830_update_table_reports_add_new_journals extends Migration
{
    private $tableName = 'public.reports';

    private $records = [
        'Журнал гематологических исследований',
        'Журнал регистрации платных ветеринарных услуг животным',
        'Журнал по оказанию ветеринарных услуг бригадами неотложной ветеринарной помощи',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->resetSequences();

        foreach ($this->records as $name) {
            $this->db
                ->createCommand()
                ->insert('{{%' . $this->tableName . '}}', [
                    'name' => $name,
                    'report_type' => 'J',
                ])
                ->execute();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        foreach ($this->records as $name) {
            $this->db
                ->createCommand()
                ->delete('{{%' . $this->tableName . '}}', [
                    'name' => $name,
                    'report_type' => 'J',
                ])
                ->execute();
        }

        $this->resetSequences();
    }

    private function resetSequences()
    {
        $tables = [
            'reports',
        ];

        foreach ($tables as $table) {
            $max = (new Query())->from($table)->max('id');
            $max = (int)$max + 1;
            $this->db->createCommand("SELECT pg_catalog.setval('public.{$table}_id_seq', {$max}, false);")->execute();
        }
    }
}
