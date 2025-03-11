<?php

use app\commands\migrate\Migration;

/**
 * Class m191015_091918_fix_organizations_fields
 */
class m191015_091918_fix_organizations_fields extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('organizations', 'public_services_available');
        $this->addColumn('organizations', 'public_services_available', $this->boolean()->notNull()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m191015_091918_fix_organizations_fields cannot be reverted.\n";

        return false;
    }

}
