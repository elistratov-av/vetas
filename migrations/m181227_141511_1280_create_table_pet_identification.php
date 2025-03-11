<?php

use app\commands\migrate\Migration;

use yii\db\Query;

/**
 * Class m181227_141511_1280_create_table_pet_identification
 */
class m181227_141511_1280_create_table_pet_identification extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('pet_identification', [
            'id' => $this->primaryKey(),
            'id_pet' => $this->integer()->notNull(),
            'id_ident_type' => $this->integer()->notNull(),
            'identification_code' => $this->string(50)->notNull(),
            'main_flag' => $this->boolean()->notNull()->defaultValue(false)
        ]);

        // creates index for column `id_ident_type`
        $this->createIndex(
            'idx-pet_identification-id_ident_type',
            'pet_identification',
            'id_ident_type'
        );

        // add foreign key for table `identification_types`
        $this->addForeignKey(
            'fk-pet_identification-id_ident_type',
            'pet_identification',
            'id_ident_type',
            'identification_types',
            'id',
            'CASCADE'
        );

        // creates index for column `id_pet`
        $this->createIndex(
            'idx-pet_identification-id_pets',
            'pet_identification',
            'id_pet'
        );

        // add foreign key for table `pets`
        $this->addForeignKey(
            'fk-pet_identification-id_pet',
            'pet_identification',
            'id_pet',
            'pets',
            'id',
            'CASCADE'
        );

        $this->createIndex(
            'idx_pet_identification_identification_code',
            'pet_identification',
            'identification_code'
        );

        $chip_id = (new Query())
            ->select('id')
            ->from('identification_types')
            ->where(['name' => 'чип'])
            ->scalar()
        ;

        if (empty($chip_id)){
            throw new \Exception('Cant find chip id in  identification_types table');
        }

        // Uniq chip
        $create_partial_uniq_chip_index = '
        CREATE UNIQUE INDEX uniq_chip_in_pet_identification 
                ON pet_identification (id_ident_type, identification_code) 
                WHERE id_ident_type = ' . $chip_id;

        $this->execute($create_partial_uniq_chip_index);

        /** Uniq main_flag=true
        $create_partial_uniq_main_flag_index = '
        CREATE UNIQUE INDEX uniq_main_flag_in_pet_identification 
                ON pet_identification (id_pet, main_flag) 
                WHERE main_flag = TRUE';

        $this->execute($create_partial_uniq_main_flag_index);
        */

        $sql =<<<SQL
CREATE FUNCTION set_uniq_main_flag_in_pet_identification()
RETURNS trigger AS \$BODY\$

BEGIN
IF NEW.main_flag = TRUE THEN
	UPDATE pet_identification SET main_flag = FALSE WHERE id_pet = NEW.id_pet;
END IF;
	RETURN NEW;
END;

\$BODY\$
LANGUAGE 'plpgsql';
SQL;

        $this->execute($sql);
        $this->execute("
            CREATE TRIGGER 
                trigger_set_uniq_main_flag_in_pet_identification 
            BEFORE INSERT OR UPDATE ON pet_identification 
            FOR EACH ROW EXECUTE PROCEDURE 
                set_uniq_main_flag_in_pet_identification();"
        );

    }


    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('DROP TRIGGER 
            trigger_set_uniq_main_flag_in_pet_identification
            ON pet_identification
        ');
        $this->execute('DROP FUNCTION
            set_uniq_main_flag_in_pet_identification
        ');
        $this->dropTable("pet_identification");
        /*echo "m181227_141511_1280_create_table_pet_identification cannot be reverted.\n";

        return false;*/
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181227_141511_1280_create_table_pet_identification cannot be reverted.\n";

        return false;
    }
    */
}
