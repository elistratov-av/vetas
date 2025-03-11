<?php

use app\commands\migrate\Migration;
use app\models\db\Params;

/**
 * Class m190817_093809_2038_add_petregdate_param
 */
class m190817_093809_2038_add_petregdate_param extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $columns = [
            'name' => 'Дата регистрации животного',
            'tech_name' => 'P0_Petregdate',
            'datatype' => 'dttm',
            'visit_flag' => true,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        Yii::$app->db->createCommand()
            ->insert(Params::tableName(), $columns)
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        Yii::$app->db->createCommand()
            ->delete(Params::tableName(), ['tech_name' => 'P0_Petregdate'])
            ->execute();
    }
}
