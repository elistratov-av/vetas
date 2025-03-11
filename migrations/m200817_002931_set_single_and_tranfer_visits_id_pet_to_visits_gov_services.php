<?php

use app\commands\migrate\Migration;

/**
 * Class m200817_002931_set_single_and_tranfer_visits_id_pet_to_visits_gov_services
 */
class m200817_002931_set_single_and_tranfer_visits_id_pet_to_visits_gov_services extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk-visits-id_pet', 'public.visits');
        $this->dropIndex('idx-visits-id_pet', 'public.visits');

        $this->update('public.visits', ['variety' => \app\models\db\Visits::VISIT_SINGLE]);

        $this->execute('update public.visits_gov_services vgs set id_pet = v.id_pet from public.visits v where vgs.id_visit = v.id');

        $this->execute('insert into public.visit_pets (id_pet, id_visit, created_by, created_at) select id_pet, id, created_by, created_at from public.visits order by id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200817_002931_set_single_and_tranfer_visits_id_pet_to_visits_gov_services cannot be reverted.\n";

        return false;
    }
}
