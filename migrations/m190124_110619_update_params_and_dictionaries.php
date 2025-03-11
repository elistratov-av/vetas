<?php

use app\commands\migrate\Migration;

/**
 * Class m190124_110619_update_params_and_dictionaries
 */
class m190124_110619_update_params_and_dictionaries extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $columns = [
            'name' => 'Клеймо',
            'tech_name' => 'P0_Petstampidentificationcode',
            'datatype' => 'text',
            'datatype_details' => '50',
            'visit_flag' => true,
        ];

        $this->db->createCommand()
            ->insert(\app\models\db\Params::tableName(), $columns)
            ->execute();

        foreach (['увеличена', 'не увеличена'] as $name) {
            $columns = [
                'name' => $name,
                'type' => 'ultrasoundsystemsize',
            ];
            $this->db->createCommand()
                ->insert(\app\models\db\Dictionaries::tableName(), $columns)
                ->execute();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190124_110619_update_params_and_dictionaries cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190124_110619_update_params_and_dictionaries cannot be reverted.\n";

        return false;
    }
    */
}
