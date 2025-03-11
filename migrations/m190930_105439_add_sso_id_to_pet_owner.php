<?php

use app\commands\migrate\Migration;

/**
 * Class m190930_105439_add_sso_id_to_pet_owner
 */
class m190930_105439_add_sso_id_to_pet_owner extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('pet_owners', 'sso_id', $this->string()->defaultValue(null));
        $this->fillSsoId();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('pet_owners', 'sso_id');
    }

    protected function fillSsoId()
    {
        $sql = <<<SQL
select 
    distinct
    message.message->'xml'->'CoordinateDataMessage'->'SignService'->'Contacts'->'BaseDeclarant'->>'SsoId' as sso_id,
    pet_owners.id
from etp.message as message
join visits on visits.id = message.visit_id
join pet_owners on pet_owners.id = visits.id_owner
where 
    message.last_name != 'Инкогнито' and message.first_name != 'Инкогнито'
    and message.message->'xml'->'CoordinateDataMessage'->'SignService'->'Contacts'->'BaseDeclarant'->>'SsoId' != 'unauthorized'
order by pet_owners.id
SQL;

        $rows = $this->db->createCommand($sql)->queryAll();
        foreach ($rows as $row) {
            $this->update('pet_owners', ['sso_id' => $row['sso_id']], ['id' => $row['id']]);
        }
    }
}
