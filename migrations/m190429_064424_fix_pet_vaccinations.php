<?php

use app\commands\migrate\Migration;

/**
 * Class m190429_064424_fix_pet_vaccinations
 */
class m190429_064424_fix_pet_vaccinations extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute(
            'ALTER TABLE pet_rabies_vaccination ALTER COLUMN batch DROP NOT NULL'
        );

        $this->execute(
            'ALTER TABLE pet_other_vaccinations ALTER COLUMN batch DROP NOT NULL'
        );

        $this->execute(
            'ALTER TABLE pet_rabies_vaccination ALTER COLUMN expiry_date DROP NOT NULL'
        );

        $this->execute(
            'ALTER TABLE pet_other_vaccinations ALTER COLUMN expiry_date DROP NOT NULL'
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute(
            'ALTER TABLE pet_rabies_vaccination ALTER COLUMN batch SET NOT NULL'
        );

        $this->execute(
            'ALTER TABLE pet_other_vaccinations ALTER COLUMN batch SET NOT NULL'
        );

        $this->execute(
            'ALTER TABLE pet_rabies_vaccination ALTER COLUMN expiry_date SET NOT NULL'
        );

        $this->execute(
            'ALTER TABLE pet_other_vaccinations ALTER COLUMN expiry_date SET NOT NULL'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190429_064424_fix_pet_vaccinations cannot be reverted.\n";

        return false;
    }
    */
}
