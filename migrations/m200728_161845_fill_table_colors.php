<?php

use app\commands\migrate\Migration;

/**
 * Class m200728_161845_fill_table_colors
 */
class m200728_161845_fill_table_colors extends Migration
{
    private $tableName = 'public.colors';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $colors = [
            ['черный'],
            ['белый'],
            ['лиловый'],
            ['рыжий'],
            ['кремовый'],
            ['темно-коричневый'],
            ['светло-коричневый'],
            ['молочный'],
            ['чалый'],
            ['тигровый'],
            ['пегий'],
            ['черно-белый'],
            ['чепрачный'],
            ['мраморный'],
            ['абрикосовый'],
            ['палевый'],
        ];

        $this->batchInsert($this->tableName, ['name'], $colors);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->truncateTable($this->tableName);
        Yii::$app->db->createCommand()->resetSequence($this->tableName, 1);
    }
}
