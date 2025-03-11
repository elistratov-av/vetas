<?php

use app\commands\migrate\Migration;

/**
 * Class m191015_113005_drop_animalId_owners_view
 */
class m191015_113005_drop_animalId_owners_view extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
drop view animalid.view_owners
SQL;
        $this->execute($sql);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m191015_113005_drop_animalId_owners_view cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191015_113005_drop_animalId_owners_view cannot be reverted.\n";

        return false;
    }
    */
}
