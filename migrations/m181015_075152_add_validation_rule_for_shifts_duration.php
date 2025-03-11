<?php

use app\commands\migrate\Migration;
use app\models\db\Shifts;
use yii\db\Expression;

/**
 * Class m181015_075152_add_validation_rule_for_shifts_duration
 */
class m181015_075152_add_validation_rule_for_shifts_duration extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        Shifts::updateAll(['duration' => new Expression('abs(duration)')]);
        Shifts::updateAll(['duration' => 1440], ['>=', 'duration', 1440]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }
}
