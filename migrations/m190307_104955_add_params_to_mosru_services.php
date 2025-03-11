<?php

use app\commands\migrate\Migration;

/**
 * Class m190307_104955_add_params_to_mosru_services
 */
class m190307_104955_add_params_to_mosru_services extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $services = (new \yii\db\Query())->from('gov_services')
            ->select(['id'])
            ->where(['type' => 'mosru'])
            ->andWhere(['at_home' => true])
            ->all();

        $param = \app\models\db\Params::findOne(['tech_name' => 'P0_Juraddress']);
        foreach ($services as $service) {
            $this->insert('gov_services_params', [
                'id_param' => $param->id,
                'id_service' => $service['id'],
                'req_in' => false,
                'req_out' => false,
                'sort_by' => 1
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $param = \app\models\db\Params::findOne(['tech_name' => 'P0_Juraddress']);
        $this->execute("DELETE FROM gov_services_params 
            WHERE id_param = :id_param 
            AND id_service IN (SELECT id from gov_services WHERE type = 'mosru' AND at_home = true)", [
                'id_param' => $param->id
        ]);
    }
}
