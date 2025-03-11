<?php

use app\commands\migrate\Migration;

/**
 * Class m190418_041701_update_visits_1786_old_visits_cancel_initiator
 */
class m190418_041701_update_visits_1786_old_visits_cancel_initiator extends Migration
{
    private $tableName = 'visits';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update(
            '{{%' . $this->tableName . '}}',
            ['cancel_initiator' => \app\models\db\Visits::INITIATOR_IS_CLINIC],
            [
                'status' => \app\common\models\VisitStatus::CANCELED,
                'cancel_initiator' => null,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->update(
            '{{%' . $this->tableName . '}}',
            ['cancel_initiator' => null]
        );
    }
}
