<?php

use app\commands\migrate\Migration;

/**
 * Class m240828_083355_copy_pet_owners_to_archive
 */
class m240828_083355_copy_pet_owners_to_archive extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->execute('CREATE TABLE archive.pet_owners (LIKE public.pet_owners INCLUDING ALL)');
        $this->addColumn('archive.pet_owners', 'result_id', $this->integer());
        $this->addForeignKey(
            'fk-archive_pet_owners-result_id',
            'archive.pet_owners',
            'result_id',
            'public.pet_owners',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-archive_pet_owners-result_id', 'archive.pet_owners');
        $this->dropTable('archive.pet_owners');
    }
}
