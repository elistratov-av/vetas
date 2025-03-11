<?php

use app\commands\migrate\Migration;

/**
 * Class m231006_065511_truncate_shift_type_invalid_intersections
 */
class m231006_065511_truncate_shift_type_invalid_intersections extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        Yii::$app->db->createCommand()
            ->truncateTable('shift_type_invalid_intersections')
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m231006_065511_truncate_shift_type_invalid_intersections cannot be reverted.\n";

        return false;
    }
}
