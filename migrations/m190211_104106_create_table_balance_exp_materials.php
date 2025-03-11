<?php

use app\commands\migrate\Migration;

/**
 * Class m190211_104106_create_table_balance_exp_materials
 */
class m190211_104106_create_table_balance_exp_materials extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('balance_exp_materials', [
            'id' => $this->primaryKey(),
            'id_organization' => $this->integer()->notNull(),
            'id_exp_materials' => $this->integer()->notNull(),
            'inventory_number' => $this->string(255)->notNull(),
            'registration_date' => $this->date(),
            'count' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->date(),
            'updated_at' => $this->date(),
        ]);

        $this->addColumn(
            'balance_exp_materials',
            'price',
            $this->decimal(8, 2)->notNull()
        );
        $this->execute('ALTER TABLE balance_exp_materials ADD CONSTRAINT check_price CHECK (price >= 0)');
        $this->addCommentOnColumn(
            'balance_exp_materials',
            'price',
            'Цена'
        );


        $this->createIndex(
            'id_organization_inventory_number_unique',
            'balance_exp_materials',
            ['id_organization', 'inventory_number'],
            true
        );


        $this->createIndex(
            'idx-balance_exp_materials-id_organization',
            'balance_exp_materials',
            'id_organization'
        );


        $this->addForeignKey(
            'fk-balance_exp_materials-id_organization',
            'balance_exp_materials',
            'id_organization',
            'organizations',
            'id',
            'CASCADE'
        );


        $this->createIndex(
            'idx-balance_exp_materials-id_equipment',
            'balance_exp_materials',
            'id_exp_materials'
        );


        $this->addForeignKey(
            'fk-balance_exp_materials-exp_materials',
            'balance_exp_materials',
            'id_exp_materials',
            'exp_materials',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190211_104106_create_table_balance_exp_materials cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190211_104106_create_table_balance_exp_materials cannot be reverted.\n";

        return false;
    }
    */
}
