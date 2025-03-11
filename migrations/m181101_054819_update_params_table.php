<?php

use app\commands\migrate\Migration;

/**
 * Class m181101_054819_update_params_table
 */
class m181101_054819_update_params_table extends Migration
{
    private $tableName = 'public.params';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $params = [
            'P34_Fattyacidsvalue',
            'P17_Petcolor',
        ];

        foreach ($params as $tech_name) {
            $this->update('{{%' . $this->tableName . '}}', ['datatype' => 'text', 'datatype_details' => 255], ['tech_name' => $tech_name]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $params = [
            'P34_Fattyacidsvalue' => 'coprfattyacids',
            'P17_Petcolor' => 'petcolor',
        ];

        foreach ($params as $tech_name => $datatype_details) {
            $this->update('{{%' . $this->tableName . '}}', ['datatype' => 'dict', 'datatype_details' => $datatype_details], ['tech_name' => $tech_name]);
        }
    }
}
