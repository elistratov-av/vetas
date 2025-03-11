<?php

use app\commands\migrate\Migration;
use app\models\db\PetRefSize;
use app\models\db\Pets;

class m211217_144241_add_size_ref extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableName = Pets::tableName();

        $this->execute("update $tableName set size_id = null where size_id is not null");
        $this->addForeignKey(
            'fk-pets-size_id',
            Pets::tableName(),
            'size_id',
            PetRefSize::tableName(),
            'id',
            'SET NULL'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-pets-size_id', Pets::tableName());
    }
}
