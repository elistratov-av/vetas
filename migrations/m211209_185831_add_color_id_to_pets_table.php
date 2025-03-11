<?php

use app\commands\migrate\Migration;
use app\models\db\PetRefColor;
use app\models\db\Pets;

/**
 * Class m211209_185831_add_color_id_to_pets_table
 */
class m211209_185831_add_color_id_to_pets_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableName = Pets::tableName();

        $this->addColumn($tableName, 'color_id', $this->integer());
        $this->addForeignKey(
            'fk-pets-color_id',
            $tableName,
            'color_id',
            PetRefColor::tableName(),
            'id',
            'SET NULL'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-pets-color_id', Pets::tableName());
        $this->dropColumn(Pets::tableName(), 'color_id');
    }
}
