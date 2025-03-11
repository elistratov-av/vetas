<?php

use app\commands\migrate\Migration;

/**
 * Class m190605_114925_update_fias_addresses_add_geo_trigger
 */
class m190605_114925_update_fias_addresses_add_geo_trigger extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE OR REPLACE FUNCTION fias_addresses_update_coords_func()
RETURNS trigger
LANGUAGE plpgsql
AS $$
BEGIN
    IF (NEW.lat IS NULL OR NEW.lon IS NULL )
    THEN NEW.coords = NULL;
    ELSE NEW.coords = CONCAT('POINT(', NEW.lon, ' ', NEW.lat, ')');
    END IF;
    RETURN NEW;
END;
$$;
SQL;
        $this->execute($sql);

        $sql = <<<SQL
CREATE TRIGGER fias_addresses_update_coords_trg
BEFORE INSERT OR UPDATE
ON fias_addresses
FOR EACH ROW
EXECUTE PROCEDURE fias_addresses_update_coords_func();
SQL;

        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('DROP TRIGGER IF EXISTS fias_addresses_update_coords_trg ON fias_addresses;');
        $this->execute('DROP FUNCTION IF EXISTS fias_addresses_update_coords_func;');
    }
}
