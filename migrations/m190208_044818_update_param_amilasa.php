<?php

use app\commands\migrate\Migration;

/**
 * Class m190208_044818_update_param_amilasa
 */
class m190208_044818_update_param_amilasa extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $params = [
            'P36_Amilazavalue' => 'ά- амилаза - результат исследований',
            'P37_Amilazadesc' => 'ά- амилаза - примечание',
        ];

        foreach ($params as $tech_name => $name) {
            $this->db->createCommand()
                ->update(\app\models\db\Params::tableName(), ['name' => $name], ['tech_name' => $tech_name])
                ->execute();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190208_044818_update_param_amilasa cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190208_044818_update_param_amilasa cannot be reverted.\n";

        return false;
    }
    */
}
