<?php

use yii\db\Migration;

/**
 * Handles the creation of table `shift_type`.
 */
class m180815_172106_create_shift_type_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('shift_type', [
            'id' => $this->primaryKey(),
            'parent_id' => $this->integer(),
            'type' => $this->string(32)->notNull()->unique()->comment('Константа типа'),
            'idle' => $this->boolean()->defaultValue(false)->notNull()->comment('Праздничный/выходной день/обед'),
            'description' => $this->string(256)->notNull()->comment('Описание'),
        ]);

        $this->addForeignKey(
            'fk-shift_type_parent_shift_type',
            'shift_type',
            'parent_id',
            'shift_type',
            'id'
        );

        $this->insert('shift_type',[
            'parent_id' => NULL,
            'type' => 'WORKDAY',
            'idle' => FALSE,
            'description' => 'Рабочий день',
        ]);

        $WORKDAY_id = $this->getDb()->lastInsertID;

        $this->insert('shift_type',[
            'parent_id' => $WORKDAY_id,
            'type' => 'MOSRU_APPOINTMENT',
            'idle' => FALSE,
            'description' => 'Запись с mos.ru',
        ]);

        $this->insert('shift_type',[
            'parent_id' => $WORKDAY_id,
            'type' => 'PHONE_APPOINTMENT',
            'idle' => FALSE,
            'description' => 'Запись по телефону',
        ]);

        $this->insert('shift_type',[
            'parent_id' => $WORKDAY_id,
            'type' => 'LIVE_QUEUE',
            'idle' => FALSE,
            'description' => 'Живая очередь',
        ]);

        $this->insert('shift_type',[
            'parent_id' => $WORKDAY_id,
            'type' => 'BREAK',
            'idle' => TRUE,
            'description' => 'Обед',
        ]);

        $this->insert('shift_type',[
            'parent_id' => NULL,
            'type' => 'SICK_LEAVE',
            'idle' => TRUE,
            'description' => 'Больничный',
        ]);

        $this->insert('shift_type',[
            'parent_id' => NULL,
            'type' => 'VACATION',
            'idle' => TRUE,
            'description' => 'Отпуск',
        ]);

        $this->insert('shift_type',[
            'parent_id' => NULL,
            'type' => 'HOLIDAY',
            'idle' => TRUE,
            'description' => 'Выходной',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-shift_type_parent_shift_type', 'shift_type');
        $this->dropTable('shift_type');
    }
}
