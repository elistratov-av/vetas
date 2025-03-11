<?php

use app\commands\migrate\Migration;

/**
 * Class m210314_152411_3296_tmc_category
 */
class m210314_152411_3296_tmc_category extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('tmc.category',[
            'id' => $this->primaryKey(),

            'name' => $this->string(255)->notNull()->unique(),
            'description' => $this->string(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->timestamp(0),
            'updated_at' => $this->timestamp(0),
        ]);

        $this->addCommentOnTable(
            'tmc.category',
            'Категории для ТМЦ'
        );

        // -----------------------СВЯЗЬ----------------------------------
        $table_name = 'tmc.category_to_tmc';

        $this->createTable('tmc.category_to_tmc',[
            'id' => $this->primaryKey(),

            'id_category' => $this->integer()->notNull(),
            'id_tmc' => $this->integer()->notNull(),
            'type_tmc' => 'tmc.tmc_class_list NOT NULL',

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->timestamp(0),
            'updated_at' => $this->timestamp(0),
        ]);
        $this->addCommentOnTable(
            'tmc.category_to_tmc',
            'Связь категории для ТМЦ - препараты'
        );
        $this->addForeignKey(
            "fk-tmc_category_to_tmc-category",
            'tmc.category_to_tmc',
            'id_category',
            'tmc.category',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            "fk-tmc_category_to_tmc-tmc",
            'tmc.category_to_tmc',
            ['id_tmc', 'type_tmc'],
            'tmc.tmc',
            ['id', 'type'],
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex(
            "uniq-tmc_category_to_tmc-row",
            'tmc.category_to_tmc',
            ['id_category', 'id_tmc', 'type_tmc'],
            true
        );

        // на данный момент у оборудования нет категорий
        $this->execute("
ALTER TABLE tmc.category_to_tmc
  ADD CONSTRAINT type_tmc_check 
    CHECK (
        type_tmc IN ('vaccine'::tmc.tmc_class_list, 'drug'::tmc.tmc_class_list, 'exp_material'::tmc.tmc_class_list)
        )
    ");


        // ---------------------УСЛУГИ-----------------------------------
        $this->createTable('tmc.category_to_gov_services',[
            'id' => $this->primaryKey(),

            'id_category' => $this->integer()->notNull(),
            'id_service' => $this->integer()->notNull(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->timestamp(0),
            'updated_at' => $this->timestamp(0),
        ]);
        $this->addCommentOnTable(
            'tmc.category_to_gov_services',
            'Связь категории для ТМЦ - услуги'
        );
        $this->addForeignKey(
            "fk-tmc_category_to_gov_services-tmc_category",
            'tmc.category_to_gov_services',
            'id_category',
            'tmc.category',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            "fk-tmc_category_to_gov_services-gov_services",
            'tmc.category_to_gov_services',
            'id_service',
            'gov_services',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex(
            "uniq-tmc_category_to_gov_services-row",
            'tmc.category_to_gov_services',
            ['id_category', 'id_service'],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('tmc.category_to_gov_services');
        $this->dropTable('tmc.category_to_tmc');
        $this->dropTable('tmc.category');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210314_152411_3296_tmc_category cannot be reverted.\n";

        return false;
    }
    */
}
