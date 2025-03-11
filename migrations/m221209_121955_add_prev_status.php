<?php

use app\commands\migrate\Migration;

/**
 * Class m221209_121955_add_prev_status
 */
class m221209_121955_add_prev_status extends Migration
{
    public function safeUp()
    {
        $this->addColumn('shelter_guests', 'prev_status', $this->string()->defaultValue(null));
        
        $this->execute("UPDATE shelter_guests g 
        SET prev_status = 'DEPARTURED' 
        WHERE g.status = 'QUARANTINE'
         AND EXISTS (SELECT 1 FROM pet_history h WHERE h.id_pet = g.id_pet AND h.EVENT = 'RETURN_PET')");
    }

    public function safeDown()
    {
        $this->dropColumn('shelter_guests', 'prev_status');
    }


}
