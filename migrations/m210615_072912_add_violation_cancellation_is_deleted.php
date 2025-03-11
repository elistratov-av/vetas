<?php

use app\commands\migrate\Migration;
use yii\helpers\Console;

/**
 * Class m210615_072912_add_violation_cancellation_is_deleted
 */
class m210615_072912_add_violation_cancellation_is_deleted extends Migration
{
    private $newCancellations = [
        'падеж',
        'животное передано другому владельцу',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('violation_cancellation', 'is_deleted', $this->boolean()->defaultValue(false)->notNull()->comment('Запись удалена'));
        $violationCancellationsToDelete = \app\models\db\ViolationCancellation::find()
            ->where(['tech_name' => null])
            ->andWhere(['is_need_cancellation_details' => false])
            ->all()
        ;
        forEach($violationCancellationsToDelete as $cancellation) {
            $cancellation->is_deleted = true;
            $cancellation->save();
        }
        $violationCancellationOther = \app\models\db\ViolationCancellation::find()
            ->andWhere(['is_need_cancellation_details' => true])
            ->one()
        ;
        $violationCancellationOther->description = 'отменить нарушение по другой причине';
        $violationCancellationOther->save();

        forEach ($this->newCancellations as $cancellationDescription) {
            try {
                $this->db->createCommand()->insert('violation_cancellation', [
                    'description' => $cancellationDescription,
                ])->execute();
            } catch (\Throwable $e) {
                Console::output(Console::ansiFormat($e->getMessage(), [Console::FG_RED]));
                throw $e;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $violationCancellationOther = \app\models\db\ViolationCancellation::find()
            ->andWhere(['is_need_cancellation_details' => true])
            ->one()
        ;
        $violationCancellationOther->description = 'иное';
        $violationCancellationOther->save();

        forEach($this->newCancellations as $cancellationDescription)
        try {
            $this->db->createCommand()->delete(
                'violation_cancellation',
                'description = :description',
                [
                    'description' => $cancellationDescription,
                ]
            )->execute();
        } catch (\Throwable $e) {
            Console::output(Console::ansiFormat($e->getMessage(), [Console::FG_RED]));
        }
        $this->dropColumn('violation_cancellation', 'is_deleted');
    }
}
