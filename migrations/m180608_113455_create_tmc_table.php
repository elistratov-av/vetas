<?php

use yii\db\Migration;

/**
 * Handles the creation of table `tmc`.
 */
class m180608_113455_create_tmc_table extends Migration
{
    const TMC_TABLE_NAME = 'tmc';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(self::TMC_TABLE_NAME, [
            'id' => $this->primaryKey(),
            'name' => $this->string(50)->unique()->notNull(),
            'id_tmc_type' => $this->integer()->comment('Тип ТМЦ')
        ]);
        $this->addCommentOnTable(self::TMC_TABLE_NAME, 'Товарно материальные ценности');

        $this->addForeignKey(
            'fk-' . self::TMC_TABLE_NAME . '-tmc_types',
            self::TMC_TABLE_NAME,
            'id_tmc_type',
            'tmc_types',
            'id',
            'SET NULL',
            'NO ACTION'
        );

        $this->createIndex(
            'idx-' . self::TMC_TABLE_NAME . '-id_tmc_type',
            self::TMC_TABLE_NAME,
            'id_tmc_type'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(self::TMC_TABLE_NAME);
    }
}
