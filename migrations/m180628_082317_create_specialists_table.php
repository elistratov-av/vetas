<?php

use yii\db\Migration;

/**
 * Handles the creation of table `specialists`.
 * Has foreign keys to the tables:
 *
 * - `files`
 * - `organizations`
 */
class m180628_082317_create_specialists_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('specialists', [
            'id' => $this->primaryKey(),
            'f_fio' => $this->string(150)->notNull(),
            'i_fio' => $this->string(50)->notNull(),
            'o_fio' => $this->string(50),
            'reg_date' => $this->date()->notNull(),
            'birthday' => $this->date()->notNull(),
            'expel_date' => $this->date(),
            'sex' => $this->string(1)->notNull(),
            'photo' => $this->integer(),
            'id_organization' => $this->integer(),
        ]);

        $this->createIndex(
            'idx-fio',
            'specialists',
            ['f_fio', 'i_fio', 'o_fio']
        );

        // creates index for column `photo`
        $this->createIndex(
            'idx-specialists-photo',
            'specialists',
            'photo'
        );

        // add foreign key for table `files`
        $this->addForeignKey(
            'fk-specialists-photo',
            'specialists',
            'photo',
            'files',
            'id',
            'CASCADE'
        );

        // creates index for column `id_organization`
        $this->createIndex(
            'idx-specialists-id_organization',
            'specialists',
            'id_organization'
        );

        // add foreign key for table `organizations`
        $this->addForeignKey(
            'fk-specialists-id_organization',
            'specialists',
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
        // drops foreign key for table `files`
        $this->dropForeignKey(
            'fk-specialists-photo',
            'specialists'
        );

        // drops index for column `photo`
        $this->dropIndex(
            'idx-specialists-photo',
            'specialists'
        );

        // drops foreign key for table `organizations`
        $this->dropForeignKey(
            'fk-specialists-id_organization',
            'specialists'
        );

        // drops index for column `id_organization`
        $this->dropIndex(
            'idx-specialists-id_organization',
            'specialists'
        );

        $this->dropTable('specialists');
    }
}
