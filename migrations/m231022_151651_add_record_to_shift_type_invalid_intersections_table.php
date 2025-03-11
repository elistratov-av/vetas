<?php

use app\commands\migrate\Migration;
use yii\helpers\BaseConsole;
use yii\helpers\Console;
use yii\db\Query;

/**
 * Class m231022_151651_add_record_to_shift_type_invalid_intersections_table
 */
class m231022_151651_add_record_to_shift_type_invalid_intersections_table extends Migration
{
    /**
     * {@inheritdoc}
     * @throws \yii\db\Exception|Throwable
     */
    public function safeUp()
    {
        Console::output(Console::ansiFormat("Загрузка Недопустимые пересечения в графике работы", [
            BaseConsole::FG_YELLOW, BaseConsole::BOLD
        ]));

        $query = new Query();
        $rows = $query
            ->select(['id'])
            ->from('public.shift_type')
            ->where(['type' => [
                'CALL_TO_HOME',
                'AMBULANCE',
                'MOSRU_CALL_TO_HOME',
                'SHELTER',
                'WORKDAY',
                'SICK_LEAVE',
                'VACATION'
            ]])
            ->all();
        foreach ($rows as $row) {
            if (!empty($row['id'])) {
                try {
                    $command = Yii::$app->db->createCommand();
                    $command->insert('public.shift_type_invalid_intersections', ['id_type' => $row['id']]);
                    $command->execute();
                } catch (Throwable $e) {
                    Console::output(Console::ansiFormat($e->getMessage(), [BaseConsole::FG_RED]));
                    throw $e;
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     * @throws \yii\db\Exception|Throwable
     */
    public function safeDown()
    {
        Console::output(Console::ansiFormat("Убираем добавленные ранее Недопустимые пересечения в графике работы", [
            BaseConsole::FG_YELLOW, BaseConsole::BOLD
        ]));

        $query = new Query();
        $rows = $query
            ->select(['id'])
            ->from('public.shift_type')
            ->where(['type' => [
                'CALL_TO_HOME',
                'AMBULANCE',
                'MOSRU_CALL_TO_HOME',
                'SHELTER',
                'WORKDAY',
                'SICK_LEAVE',
                'VACATION'
            ]])
            ->all();
        foreach ($rows as $row) {
            if (!empty($row['id'])) {
                try {
                    $command = Yii::$app->db->createCommand();
                    $command->delete('public.shift_type_invalid_intersections', ['id_type' => $row['id']]);
                    $command->execute();
                } catch (Throwable $e) {
                    Console::output(Console::ansiFormat($e->getMessage(), [BaseConsole::FG_RED]));
                    throw $e;
                }
            }
        }
    }
}
