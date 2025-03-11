<?php

use app\commands\migrate\Migration;
use app\models\db\Params;

/**
 * Class m240814_132418_update_params_table
 */
class m240814_132418_update_params_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $param = Params::findOne(['tech_name' => 'P13_Serviceresultdesc']);
        if (!$param) return false;
        $param->datatype_details = '3000';
        return $param->save();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $param = Params::findOne(['tech_name' => 'P13_Serviceresultdesc']);
        if (!$param) return false;
        $param->datatype_details = '255';
        return $param->save();
    }
}
