<?php

use app\commands\migrate\Migration;

/**
 * Class m190820_232338_alt_put_flag_capital_structure
 */
class m190820_232338_alt_put_flag_capital_structure extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $orgIds = (new \yii\db\Query())->select('id')
            ->from('organizations')
            ->where(['or',
                ['point_utilization' => true],
                ['point_vaccination' => true],
                ['point_registration' => true]
            ])->column();

        foreach ($orgIds as $id) {
            $this->update('organizations', ['capital_structure' => true], ['id' => $id]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $orgIds = (new \yii\db\Query())->select('id')
            ->from('organizations')
            ->where(['or',
                ['point_utilization' => true],
                ['point_vaccination' => true],
                ['point_registration' => true]
            ])->column();

        foreach ($orgIds as $id) {
            $this->update('organizations', ['capital_structure' => false], ['id' => $id]);
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190820_232338_alt_put_flag_capital_structure cannot be reverted.\n";

        return false;
    }
    */
}
