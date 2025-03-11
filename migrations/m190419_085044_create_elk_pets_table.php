<?php

use yii\db\Migration;

/**
 * Handles the creation of table `elk_pets`.
 */
class m190419_085044_create_elk_pets_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('elk.pets', [
            'id' => $this->primaryKey(),
            'id_pet' => $this->integer()->notNull(),
            'ext_id' => $this->string()->unique()->notNull()
        ]);

        $this->addForeignKey(
            'fk-elk_pets-ext_id',
            'elk.pets',
            'id_pet',
            'pets',
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
        $this->dropForeignKey('fk-elk_pets-ext_id', 'elk.pets');
        $this->dropTable('elk.pets');
    }
}
