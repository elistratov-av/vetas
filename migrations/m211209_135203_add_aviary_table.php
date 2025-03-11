<?php

use app\commands\migrate\Migration;

/**
 * Class m211209_135203_add_aviary_table
 */
class m211209_135203_add_aviary_table extends Migration
{
    private $tableName = 'aviary';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'aviary_number' => $this->integer(),
            'pets_moniker' => $this->string(),
            'pets_card' => $this->integer()->comment('Тестовое поле, все равно придётся апдейтить'),
            'pets_chip' => $this->integer()->comment('Со внешними ключами та же история. Апдейтить, только апдейтить...'),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m211209_135203_add_aviary_table cannot be reverted.\n";
        $this->dropTable($this->tableName);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m211209_135203_add_aviary_table cannot be reverted.\n";

        return false;
    }
    */
}
