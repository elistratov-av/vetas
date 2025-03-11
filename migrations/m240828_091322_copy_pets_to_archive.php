<?php

use app\commands\migrate\Migration;

/**
 * Class m240828_091322_copy_pets_to_archive
 */
class m240828_091322_copy_pets_to_archive extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('CREATE TABLE archive.pets (LIKE public.pets INCLUDING ALL)');
        $this->addColumn('archive.pets', 'result_id', $this->integer());
        $this->addForeignKey(
            'fk-archive_pets-result_id',
            'archive.pets',
            'result_id',
            'public.pets',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-archive_pets-result_id', 'archive.pets');
        $this->dropTable('archive.pets');
    }

}
