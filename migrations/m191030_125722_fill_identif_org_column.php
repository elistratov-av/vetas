<?php

use app\commands\migrate\Migration;

/**
 * Class m191030_125722_fill_identif_org_column
 */
class m191030_125722_fill_identif_org_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
select
    pid.id_pet,
    pets.id_reg_organization
from pet_identification as pid
left join pets on pets.id = pid.id_pet
order by pid.id_pet
SQL;

        $rows = $this->db->createCommand($sql)->queryAll();
        foreach ($rows as $row) {
            $this->update('public.pet_identification', ['identif_org' => $row['id_reg_organization']], ['id_pet' => $row['id_pet']]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
//        echo "m191030_125722_fill_identif_org_column cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191030_125722_fill_identif_org_column cannot be reverted.\n";

        return false;
    }
    */
}
