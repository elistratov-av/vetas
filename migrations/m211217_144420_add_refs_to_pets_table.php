<?php

use app\commands\migrate\Migration;
use app\models\db\PetRefSize;
use app\models\db\Pets;

class m211217_144420_add_refs_to_pets_table extends Migration
{
    private static $data = [
        'ear_type' => [],
        'tail_type' => [],
        'wool_type' => [],
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableName = Pets::tableName();

        foreach (self::$data as $key => $description) {
            $this->addColumn($tableName, "{$key}_id", $this->integer());
            $this->addForeignKey(
                "fk-pets-{$key}_id",
                $tableName,
                "{$key}_id",
                "pet_ref_{$key}",
                'id',
                'SET NULL'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $tableName = Pets::tableName();

        foreach (self::$data as $key => $description) {
            $this->dropForeignKey("fk-pets-{$key}_id", $tableName);
            $this->dropColumn($tableName, "{$key}_id");
        }
    }
}
