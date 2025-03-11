<?php

use yii\db\Migration;

/**
 * Class m180730_150108_refactor_pet_owner
 */
class m180730_150108_refactor_pet_owner extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pet_owners_src', 'is_legal', $this->boolean()->notNull()->defaultValue(false));
        $sql = <<<SQL
UPDATE pet_owners_src SET is_legal = TRUE WHERE jur_name is not null;    
SQL;
        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('pet_owners_src', 'is_legal');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180730_150108_refactor_pet_owner cannot be reverted.\n";

        return false;
    }
    */
}
