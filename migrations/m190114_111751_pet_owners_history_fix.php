<?php

use app\commands\migrate\Migration;

/**
 * Class m190114_111751_pet_owners_history_fix
 */
class m190114_111751_pet_owners_history_fix extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn(
            'pet_owners_history',
            'organization_name',
            'DROP NOT NULL'
        );
        $this->renameColumn(
            'pet_owners_history',
            'created_by_fio',
            'created_by'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameColumn(
            'pet_owners_history',
            'created_by',
            'created_by_fio'
        );
        $this->alterColumn(
            'pet_owners_history',
            'organization_name',
            'SET NOT NULL'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190114_111751_pet_owners_history_fix cannot be reverted.\n";

        return false;
    }
    */
}
