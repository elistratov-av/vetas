<?php

use app\commands\migrate\Migration;

/**
 * Class m210820_063946_transfer_id_pet_to_log_pets
 */
class m210820_063946_transfer_id_pet_to_log_pets extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
INSERT INTO subscription.log_pets (id_log, id_pet)
SELECT id, id_pet FROM subscription.log
WHERE NOT id_pet isnull AND id NOT IN (SELECT DISTINCT id_log FROM subscription.log_pets)
SQL;
        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210820_063946_transfer_id_pet_to_log_pets cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210820_063946_transfer_id_pet_to_log_pets cannot be reverted.\n";

        return false;
    }
    */
}
