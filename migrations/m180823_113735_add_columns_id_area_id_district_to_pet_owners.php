<?php

use yii\db\Migration;

/**
 * Class m180823_113735_add_columns_id_area_id_district_to_pet_owners
 */
class m180823_113735_add_columns_id_area_id_district_to_pet_owners extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pet_owners', 'id_area', $this->integer());
        $this->addColumn('pet_owners', 'id_district', $this->integer());
        
        $sql = <<<SQL
CREATE OR REPLACE FUNCTION pet_owner_id_area_id_district_func()
RETURNS trigger
LANGUAGE plpgsql
AS $$
DECLARE
    address record;
BEGIN
    IF NEW.id_address IS NOT NULL THEN
        SELECT id_area, id_district INTO STRICT address FROM addresses WHERE id = NEW.id_address;
        NEW.id_area := address.id_area;
        NEW.id_district := address.id_district;
    ELSE
        NEW.id_area := NULL;
        NEW.id_district := NULL;
    END IF;

    RETURN NEW;
END;
$$;
SQL;
        $this->execute($sql);

        $sql = <<<SQL
CREATE TRIGGER pet_owners_id_area_id_district_trg
BEFORE INSERT OR UPDATE
ON pet_owners
FOR EACH ROW
EXECUTE PROCEDURE pet_owner_id_area_id_district_func();
SQL;
    
    $this->execute($sql);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180823_113735_add_columns_id_area_id_district_to_pet_owners cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180823_113735_add_columns_id_area_id_district_to_pet_owners cannot be reverted.\n";

        return false;
    }
    */
}
