<?php

use app\commands\migrate\Migration;

/**
 * Class m190814_100253_alt_put_organizations_capital_structure_flag
 */
class m190814_100253_alt_put_organizations_capital_structure_flag extends Migration
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

        $this->update('organizations', ['capital_structure' => true], ['id' => $orgIds]);
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

        $this->update('organizations', ['capital_structure' => false], ['id' => $orgIds]);
    }
}
