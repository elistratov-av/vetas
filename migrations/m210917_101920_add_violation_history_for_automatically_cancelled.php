<?php

use app\commands\migrate\Migration;
use app\models\db\ViolationHistory;
use yii\db\Expression;

/**
 * Class m210917_101920_add_violation_history_for_automatically_cancelled
 */
class m210917_101920_add_violation_history_for_automatically_cancelled extends Migration
{
    const REASONS = [
        'Автоматическая отмена нарушения для животного снятого с учета',
        'Автоматическая отмена нарушения для животного помеченого как дубль',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $techUser = \app\models\db\Users::find()->where(['is_system_user' => true])->one();

        foreach (self::REASONS as $comment) {
            /** @var \app\models\db\Violation[] $violationClosedByReason */
            $violationClosedByReason = \app\models\db\Violation::find()->where(['comment' => $comment])->all();

            foreach ($violationClosedByReason as $violation) {
                $history_record = new ViolationHistory([
                    'id_violation' => $violation->id_violation,
                    'id_inspector' => $techUser->id,
                    'date' => new Expression('NOW()'),
                    'description' => $comment,
                ]);

                $history_record->save();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210917_101920_add_violation_history_for_automatically_cancelled cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210917_101920_add_violation_history_for_automatically_cancelled cannot be reverted.\n";

        return false;
    }
    */
}
