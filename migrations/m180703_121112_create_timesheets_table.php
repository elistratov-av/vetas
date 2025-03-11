<?php

use yii\db\Migration;

/**
 * Handles the creation of table `timesheets`.
 * Has foreign keys to the tables:
 *
 * - `specialists`
 * - `shifts`
 */
class m180703_121112_create_timesheets_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('timesheets', [
            'id' => $this->primaryKey(),
            'date' => $this->date()->notNull()->comment('Дата'),
            'id_specialist' => $this->integer()->comment('Ссылка на специалиста'),
            'id_shift' => $this->integer()->comment('Ссылка на рабочую смену'),
        ]);
        $this->addCommentOnTable('timesheets', 'Расписание приема');

        // creates index for column `id_specialist`
        $this->createIndex(
            'idx-timesheets-id_specialist',
            'timesheets',
            'id_specialist'
        );

        // add foreign key for table `specialists`
        $this->addForeignKey(
            'fk-timesheets-id_specialist',
            'timesheets',
            'id_specialist',
            'specialists',
            'id',
            'CASCADE'
        );

        // creates index for column `id_shift`
        $this->createIndex(
            'idx-timesheets-id_shift',
            'timesheets',
            'id_shift'
        );

        // add foreign key for table `shifts`
        $this->addForeignKey(
            'fk-timesheets-id_shift',
            'timesheets',
            'id_shift',
            'shifts',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `specialists`
        $this->dropForeignKey(
            'fk-timesheets-id_specialist',
            'timesheets'
        );

        // drops index for column `id_specialist`
        $this->dropIndex(
            'idx-timesheets-id_specialist',
            'timesheets'
        );

        // drops foreign key for table `shifts`
        $this->dropForeignKey(
            'fk-timesheets-id_shift',
            'timesheets'
        );

        // drops index for column `id_shift`
        $this->dropIndex(
            'idx-timesheets-id_shift',
            'timesheets'
        );

        $this->dropTable('timesheets');
    }
}
