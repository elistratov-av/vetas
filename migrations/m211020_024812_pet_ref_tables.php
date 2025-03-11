<?php

use app\commands\migrate\Migration;

/**
 * Class m211020_024812_pet_ref_tables
 */
class m211020_024812_pet_ref_tables extends Migration
{
    private static $data = [
        'color' => [],
        'ear_type' => [],
        'tail_type' => [],
        'wool_type' => [],
        // 'size' => [],
        'ground_for_disposal' => [],
        ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // $this->dropTable('pet_ref_size');
        foreach (self::$data as $key => $description){
            $tablename = 'pet_ref_' . $key;
            $conjunctionTablename = 'pets_to_' . $tablename;

            $this->createTable($tablename, [ 'id' => $this->primaryKey(), 'title' => $this->string()->notNull(),]);

            $this->createTable($conjunctionTablename,[
                'id' => $this->primaryKey(),
                'id_pets' => $this->integer()->notNull(),
                'id_' . $tablename => $this->integer()->notNull(),
            ]);

            // pets
            $this->addForeignKey(
                "fk-$conjunctionTablename-id_pets",
                $conjunctionTablename,
                'id_pets',
                'pets',
                'id',
                'NO ACTION'
            );

            // pet_ref
            $this->addForeignKey(
                "fk-$conjunctionTablename-id_$tablename",
                $conjunctionTablename,
                'id_' . $tablename,
                $tablename,
                'id',
                'NO ACTION'
            );

            $this->createIndex(
                "uniq-$conjunctionTablename-id_pets-id_$tablename",
                $conjunctionTablename,
                ['id_pets', 'id_' . $tablename],
                TRUE
            );
        }


    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        foreach (self::$data as $key => $description) {
            $tablename = 'pet_ref_' . $key;
            $conjunctionTablename = 'pets_to_' . $tablename;

            $this->dropForeignKey("fk-$conjunctionTablename-id_pets", $conjunctionTablename);
            $this->dropForeignKey("fk-$conjunctionTablename-id_$tablename", $conjunctionTablename);
            $this->dropTable($conjunctionTablename);
            $this->dropTable($tablename);
        }
    }
}
