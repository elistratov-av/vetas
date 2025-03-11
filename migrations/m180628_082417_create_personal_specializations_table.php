<?php

use yii\db\Migration;

/**
 * Handles the creation of table `personal_specializations`.
 * Has foreign keys to the tables:
 *
 * - `specialists`
 * - `specializations`
 */
class m180628_082417_create_personal_specializations_table extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('personal_specializations', [
            'id' => $this->primaryKey(),
            'id_specialist' => $this->integer(),
            'id_specialization' => $this->integer(),
        ]);

        // creates index for column `id_specialist`
        $this->createIndex(
            'idx-personal_specializations-id_specialist',
            'personal_specializations',
            ['id_specialist', 'id_specialization'], true
        );

        // add foreign key for table `specialists`
        $this->addForeignKey(
            'fk-personal_specializations-id_specialist',
            'personal_specializations',
            'id_specialist',
            'specialists',
            'id',
            'CASCADE'
        );

        // add foreign key for table `specializations`
        $this->addForeignKey(
            'fk-personal_specializations-id_specialization',
            'personal_specializations',
            'id_specialization',
            'specializations',
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
            'fk-personal_specializations-id_specialist',
            'personal_specializations'
        );

        // drops index for column `id_specialist`
        $this->dropIndex(
            'idx-personal_specializations-id_specialist',
            'personal_specializations'
        );

        // drops foreign key for table `specializations`
        $this->dropForeignKey(
            'fk-personal_specializations-id_specialization',
            'personal_specializations'
        );

        // drops index for column `id_specialization`
        $this->dropIndex(
            'idx-personal_specializations-id_specialization',
            'personal_specializations'
        );

        $this->dropTable('personal_specializations');
    }
}
