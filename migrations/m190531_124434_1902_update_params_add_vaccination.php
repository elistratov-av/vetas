<?php

use app\commands\migrate\Migration;

/**
 * Class m190531_124434_1902_update_params_add_vaccination
 */
class m190531_124434_1902_update_params_add_vaccination extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $data = [
            'name' => 'Дата вакцинации животного',
            'tech_name' => 'P0_PetRabiesVaccinationDate',
            'datatype' => 'dttm',
            'visit_flag' => true,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->db
            ->createCommand()
            ->insert('params', $data)
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->db
            ->createCommand()
            ->delete('params', ['tech_name' => 'P0_PetRabiesVaccinationDate'])
            ->execute();
    }
}
