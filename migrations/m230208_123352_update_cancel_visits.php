<?php

use app\commands\migrate\Migration;

/**
 * Class m230208_123352_update_cancel_visits
 */
class m230208_123352_update_cancel_visits extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("BEGIN; -- start transaction");

        $this->execute("CREATE TEMPORARY TABLE temp_visists ON COMMIT DROP AS 
SELECT v.id, v.id_pet, v.id_owner, p.id_pet_tmp, o.id_pet_owner_tmp
	FROM visits v
	JOIN pets p ON p.id = v.id_pet
	JOIN pet_owners o ON o.id = v.id_owner
	WHERE v.status = 'A'
		AND v.channel = 2
		AND o.sso_id IS NULL;");

	 
$this->execute("
UPDATE visits SET id_pet = null, id_owner = null 
WHERE id IN (SELECT id FROM temp_visists);");
	 
$this->execute("
UPDATE visit_pets SET id_pet = null
WHERE id_visit IN (SELECT id FROM temp_visists);");
	 
$this->execute("DELETE FROM pets
WHERE id IN (SELECT DISTINCT id_pet FROM temp_visists WHERE id_pet_tmp IS NOT NULL);	"); 

$this->execute("DELETE FROM pets_tmp
WHERE id IN (SELECT DISTINCT id_pet_tmp FROM temp_visists);	 ");
	 
$this->execute("DELETE FROM pet_owners
WHERE id IN (SELECT DISTINCT id_owner FROM temp_visists WHERE id_pet_owner_tmp IS NOT NULL);");

$this->execute("DELETE FROM pet_owners_tmp
WHERE id IN (SELECT DISTINCT id_pet_owner_tmp FROM temp_visists);");

$this->execute("COMMIT; -- drops the temp table");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230208_123352_update_cancel_visits cannot be reverted.\n";

        return false;
    }

}
