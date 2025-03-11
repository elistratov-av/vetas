<?php

use app\commands\migrate\Migration;

/**
 * Class m231010_082214_add_passport_data_to_pet_owners_table
 */
class m231010_082214_add_passport_data_to_pet_owners_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('pet_owners', 'passport');
        $this->addColumn('pet_owners', 'passport_number', $this->string()->null());
        $this->addColumn('pet_owners', 'passport_series', $this->string()->null());
        $this->addColumn('pet_owners', 'passport_issue_date', $this->date()->null());
        $this->addColumn('pet_owners', 'passport_issuer', $this->string()->null());

        $this->createIndex(
            '{{%idx-unique-pet_owners-passport_number-passport_series}}',
            '{{%pet_owners}}',
            ['passport_number', 'passport_series'],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex(
            '{{%idx-unique-pet_owners-passport_number-passport_series}}',
            '{{%pet_owners}}'
        );
        $this->dropColumn('pet_owners', 'passport_number');
        $this->dropColumn('pet_owners', 'passport_series');
        $this->dropColumn('pet_owners', 'passport_issue_date');
        $this->dropColumn('pet_owners', 'passport_issuer');
        $this->addColumn('pet_owners', 'passport', $this->string());
    }
}
