<?php

use app\commands\migrate\Migration;

/**
 * Class m230926_105735_add_gost_disease_dictionary_tables
 */
class m230926_105735_add_gost_disease_dictionary_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%gost_diseases}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(512)->comment('Наименование болезни'),
            'gost_code' => $this->string()->comment('Код заболевания по ГОСТ'),
            'id_sub_category' => $this->integer()->comment('Идентификатор подкласса болезни'),
        ]);
        $this->addCommentOnTable('gost_diseases', 'Список болезней по ГОСТ');

        $this->createTable('{{%gost_disease_sub_categories}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(512)->comment('Наименование подкласса болезни'),
            'id_category' => $this->integer()->comment('Идентификатор класса болезни'),
        ]);
        $this->addCommentOnTable('gost_disease_sub_categories', 'Подклассы болезней по ГОСТ');

        $this->createTable('{{%gost_disease_categories}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(512)->comment('Наименование класса болезни'),
        ]);
        $this->addCommentOnTable('gost_disease_categories', 'Классы болезней по ГОСТ');

        $this->addForeignKey(
            'fk-gost_disease-id_sub_category',
            'gost_diseases',
            'id_sub_category',
            'gost_disease_sub_categories',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-gost_sub_category-id_category',
            'gost_disease_sub_categories',
            'id_category',
            'gost_disease_categories',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-gost_disease-id_sub_category',
            'gost_diseases'
        );
        $this->dropForeignKey(
            'fk-gost_sub_category-id_category',
            'gost_disease_sub_categories'
        );
        $this->dropTable('{{%gost_diseases}}');
        $this->dropTable('{{%gost_disease_sub_categories}}');
        $this->dropTable('{{%gost_disease_categories}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230926_105735_add_gost_disease_dictionary_tables cannot be reverted.\n";

        return false;
    }
    */
}
