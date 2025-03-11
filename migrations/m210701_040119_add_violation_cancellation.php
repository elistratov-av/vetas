<?php

use app\commands\migrate\Migration;
use yii\helpers\Console;

/**
 * Class m210701_040119_add_violation_cancellation
 */
class m210701_040119_add_violation_cancellation extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        try {
            $this->db->createCommand()->insert('violation_cancellation', [
                'description' => 'медотвод',
            ])->execute();
        } catch (\Throwable $e) {
            Console::output(Console::ansiFormat($e->getMessage(), [Console::FG_RED]));
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        try {
            $this->db->createCommand()->delete(
                'violation_cancellation',
                'description = :description',
                [
                    'description' => 'медотвод',
                ]
            )->execute();
        } catch (\Throwable $e) {
            Console::output(Console::ansiFormat($e->getMessage(), [Console::FG_RED]));
        }
    }
}
