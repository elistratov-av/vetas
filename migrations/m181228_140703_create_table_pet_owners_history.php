<?php

use app\commands\migrate\Migration;
use yii\db\Expression;
/**
 * Class m181228_140703_create_table_pet_owners_history
 */
class m181228_140703_create_table_pet_owners_history extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('pet_owners_history', [
            'id' => $this->primaryKey(),

            'date' => $this->dateTime(0)
                ->notNull()
                ->defaultValue(new Expression(
                        "now()::timestamp without time zone"
                )),
            'id_pet' => $this->integer()->notNull(),

            'created_by_fio' => $this->string(255)->notNull(),
            'organization_name' => $this->string(255)->notNull(),
            'owner_type_old' => $this->string(255),
            'owner_type_new' => $this->string(255),
            'owner_name' => $this->string(255),
        ]);

        $this->addCommentOnTable(
            'pet_owners_history', 'История изменений владельцев'
        );

        // creates index for column `id_pet`
        $this->createIndex(
            'idx-pet_owners_history-id_pet',
            'pet_owners_history',
            'id_pet'
        );

        // add foreign key for table `id_pet`
        $this->addForeignKey(
            'fk-pet_owners_history-id_pet',
            'pet_owners_history',
            'id_pet',
            'pets',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('pet_owners_history');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181228_140703_create_table_pet_owners_history cannot be reverted.\n";

        return false;
    }
    */
}
