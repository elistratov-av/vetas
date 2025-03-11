<?php

use app\commands\migrate\Migration;

/**
 * Class m190115_080558_table_pets_modify
 */
class m190115_080558_table_pets_modify extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('pets','id_ident_type');
        $this->dropColumn('pets','identification_code');
        $this->addColumn(
            'pets',
            'guide_dog',
            $this->boolean()->notNull()->defaultValue('false')
        );

        $this->addColumn(
            'pets',
            'castrated',
            $this->boolean()->notNull()->defaultValue('false')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190115_080558_table_pets_modify cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190115_080558_table_pets_modify cannot be reverted.\n";

        return false;
    }
    */
}
