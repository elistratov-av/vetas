<?php

use app\commands\migrate\Migration;

/**
 * Class m190221_162420_table_visit_discount
 */
class m190221_162420_table_visit_discount extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('visit_discount', [
            'id' => $this->primaryKey(),
            'id_visit' => $this->integer()->notNull()->unique(),
            'id_discount' => $this->integer(),
            'night_mark_up_ratio' =>  $this->decimal(8, 2),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->timestamp(0),
            'updated_at' => $this->timestamp(0),
        ]);

        $this->addCommentOnTable(
            'visit_discount',
            'Примененнные скидки к визиту'
        );

        $this->addForeignKey(
            'fk_visit_discount_id_visit',
            'visit_discount',
            'id_visit',
            'visits',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_visit_discount_id_discount',
            'visit_discount',
            'id_discount',
            'discount',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk_visit_discount_id_visit',
            'visit_discount'
        );

        $this->dropForeignKey(
            'fk_visit_discount_id_discount',
            'visit_discount'
        );

        $this->dropTable('visit_discount');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190221_162420_table_visit_discount cannot be reverted.\n";

        return false;
    }
    */
}
