<?php

use app\commands\migrate\Migration;

/**
 * Class m190121_132455_move_chips_from_old_identification_to_pet_identification
 */
class m190121_132455_move_chips_from_old_identification_to_pet_identification extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
INSERT INTO public.pet_identification (id_pet, id_ident_type, identification_code)
SELECT oldt.id_pet, oldt.id_ident_type, oldt.identification_code
FROM temp.pets_old_identification as oldt
WHERE oldt.id_ident_type = 1
AND length(oldt.identification_code) = 15
ON CONFLICT DO NOTHING
SQL;
        $this->execute($sql);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190121_132455_move_chips_from_old_identification_to_pet_identification cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190121_132455_move_chips_from_old_identification_to_pet_identification cannot be reverted.\n";

        return false;
    }
    */
}
