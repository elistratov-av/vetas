<?php

use yii\db\Migration;

/**
 * Handles the creation of table `shifts`.
 * Has foreign keys to the tables:
 *
 * - `organizations`
 */
class m180703_120015_create_shifts_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('shifts', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()->comment('Название'),
            'from_time' => $this->time()->notNull()->comment('Время начала'),
            'idle' => $this->boolean()->comment('Праздничный/выходной день'),
            'duration' => $this->integer()->comment('Продолжительность в минутах'),
            'id_organization' => $this->integer()->comment('Ссылка на организацию'),
        ]);
        $this->addCommentOnTable('shifts', 'Рабочие смены');

        // creates index for column `id_organization`
        $this->createIndex(
            'idx-shifts-id_organization',
            'shifts',
            'id_organization'
        );

        // add foreign key for table `organizations`
        $this->addForeignKey(
            'fk-shifts-id_organization',
            'shifts',
            'id_organization',
            'organizations',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `organizations`
        $this->dropForeignKey(
            'fk-shifts-id_organization',
            'shifts'
        );

        // drops index for column `id_organization`
        $this->dropIndex(
            'idx-shifts-id_organization',
            'shifts'
        );

        $this->dropTable('shifts');
    }
}
