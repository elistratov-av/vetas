<?php

use app\commands\migrate\Migration;

/**
 * Class m211005_083615_update_visit_param_values_for_specialists
 */
class m211005_083615_update_visit_param_values_for_specialists extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("UPDATE visit_param_values VPN
SET char_value = U.fullname
FROM visits_specialists AS VS
    INNER JOIN specialists AS S ON VS.id_specialist = S.id
    INNER JOIN users AS U ON S.id_user = U.id
    INNER JOIN params AS P ON 1=1

WHERE VPN.id_visit = VS.id_visit AND P.tech_name = 'P33_SpecialistFIO' AND VPN.id_param = P.id
AND VPN.char_value = ''");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m211005_083615_update_visit_param_values_for_specialists cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m211005_083615_update_visit_param_values_for_specialists cannot be reverted.\n";

        return false;
    }
    */
}
