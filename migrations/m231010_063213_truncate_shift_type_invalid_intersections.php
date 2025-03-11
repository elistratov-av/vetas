<?php

use app\commands\migrate\Migration;

/**
 * Class m231010_063213_truncate_shift_type_invalid_intersections
 */
class m231010_063213_truncate_shift_type_invalid_intersections extends Migration
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
        echo "m231010_063213_truncate_shift_type_invalid_intersections cannot be reverted.\n";

        return false;
    }
}
