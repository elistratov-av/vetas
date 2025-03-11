<?php

use app\commands\migrate\Migration;

/**
 * Class m190115_075520_move_part_colums_frompets_to_temp_schema
 */
class m190115_075520_move_part_colums_frompets_to_temp_schema extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('CREATE SCHEMA temp;');
        $this->createTable('temp.pets_old_identification',[
            'id' => $this->primaryKey(),
            'id_pet' => $this->integer(),
            'id_ident_type' => $this->integer(),
            'identification_code' => $this->string(50),
        ]);

        $sql = '
        INSERT INTO temp.pets_old_identification (id_pet, id_ident_type, identification_code)
        SELECT 
               id AS id_pet,
               id_ident_type,
               identification_code
        FROM pets
        ';

        $this->execute($sql);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('temp.pets_old_identification');
        $this->execute('DROP SCHEMA temp;');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190115_075520_move_part_colums_frompets_to_temp_schema cannot be reverted.\n";

        return false;
    }
    */
}
