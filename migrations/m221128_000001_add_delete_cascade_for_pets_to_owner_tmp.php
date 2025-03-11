<?php

use app\commands\migrate\Migration;

/**
 * Class m221128_000001_add_delete_cascade_for_pets_to_owner_tmp
 */
class m221128_000001_add_delete_cascade_for_pets_to_owner_tmp extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk-pets_to_owner_tmp-id_owner_tmp', 'pets_to_owner_tmp');
        $this->addForeignKey(
            'fk-pets_to_owner_tmp-id_owner_tmp',
            'pets_to_owner_tmp',
            'id_owner_tmp',
            'pet_owners_tmp',
            'id',
            'CASCADE'
        );

        $this->dropForeignKey('fk-pets_to_owner_tmp-id_pet_tmp', 'pets_to_owner_tmp');
        $this->addForeignKey(
            'fk-pets_to_owner_tmp-id_pet_tmp',
            'pets_to_owner_tmp',
            'id_pet_tmp',
            'pets_tmp',
            'id',
            'CASCADE'
        );

        $this->dropForeignKey('fk-pets_to_owner_tmp-id_owner_type', 'pets_to_owner_tmp');
        $this->addForeignKey(
            'fk-pets_to_owner_tmp-id_owner_type',
            'pets_to_owner_tmp',
            'id_owner_type',
            'pet_owner_type',
            'id',
            'CASCADE'
        );

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo 'no need for reverting';

        return true;
    }
}
