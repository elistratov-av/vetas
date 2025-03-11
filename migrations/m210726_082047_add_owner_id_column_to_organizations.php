<?php

use app\commands\migrate\Migration;

/**
 * Class m210726_082047_add_owner_id_column_to_organizations
 */
class m210726_082047_add_owner_id_column_to_organizations extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('organizations', 'id_pet_owner', $this->integer()->comment('Представитель Приюта'));

        $this->addForeignKey(
            'fk-organizations_id_pet_owner',
            'organizations',
            'id_pet_owner',
            'pet_owners',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('organizations', 'id_pet_owner');
    }
}
