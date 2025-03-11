<?php

use app\commands\migrate\Migration;
use yii\db\Query;

/**
 * Class m190923_112954_2320_params_update
 */
class m190923_112954_2320_params_update extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('params', ['name' => 'Срок годности: до'], ['tech_name' => 'P15_Vacexpirationdate']);

        $param = (new Query())
            ->from('params')
            ->where(['tech_name' => 'P0_Balanceinventorynumber'])
            ->one();
        $this->update('params', ['tech_name' => 'P0_Inventorynumber'], ['id' => $param['id']]);

        $service = (new Query())
            ->from('gov_services')
            ->where(['name' => 'Вакцинация животных с проведением клинического осмотра, консультации, инъекции'])
            ->one();

        $this->insert('gov_services_params', [
            'id_param' => $param['id'],
            'id_service' => $service['id'],
            'req_out' => true,
            'flag_out' => true,
            'sort_by' => 5
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190923_112954_2320_params_update cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190923_112954_2320_params_update cannot be reverted.\n";

        return false;
    }
    */
}
