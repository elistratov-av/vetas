<?php

use app\commands\migrate\Migration;

/**
 * Class m190204_125643_create_table_discount
 */
class m190204_125643_create_table_discount extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('discount',[
            'id' => $this->primaryKey(),
            'name' => $this->char(255)->notNull()->comment('Наименование скидки'),
            'value' => $this->integer()->notNull()->comment('Значение скидки в процентах'),
            'is_deleted' => $this->boolean()->notNull()->defaultValue('false')->comment('Флаг: удалено'),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->timestamp(0),
            'updated_at' => $this->timestamp(0),
        ]);

        $this->addCommentOnTable('discount', 'Справочник скидок');

        /** Только для неудаленных уникальность по имени **/
        $create_partial_uniq_name_index = '
        CREATE UNIQUE INDEX partial_uniq_name_in_discount
        ON discount ("name", is_deleted) 
        WHERE is_deleted = FALSE';

        $this->execute($create_partial_uniq_name_index);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('partial_uniq_name_in_discount','discount');
        $this->dropTable('discount');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190204_125643_create_table_discount cannot be reverted.\n";

        return false;
    }
    */
}
