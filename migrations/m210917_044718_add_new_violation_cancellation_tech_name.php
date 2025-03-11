<?php

use app\commands\migrate\Migration;

/**
 * Class m210917_044718_add_new_violation_cancellation_tech_name
 */
class m210917_044718_add_new_violation_cancellation_tech_name extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $violationCancellationDeath = \app\models\db\ViolationCancellation::find()->where(['description' => 'падеж'])->one();
        $violationCancellationDeath->tech_name = 'death';
        $violationCancellationDeath->save();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $violationCancellationDeath = \app\models\db\ViolationCancellation::find()->where(['description' => 'падеж'])->one();
        $violationCancellationDeath->tech_name = null;
        $violationCancellationDeath->save();
    }
}
