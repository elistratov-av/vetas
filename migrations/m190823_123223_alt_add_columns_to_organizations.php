<?php

use app\commands\migrate\Migration;

/**
 * Class m190823_123223_alt_add_columns_to_organizations
 */
class m190823_123223_alt_add_columns_to_organizations extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('organizations', 'public_service_available', $this->integer());
        $this->addColumn('organizations', 'chief_name', $this->string(100));
        $this->addColumn('organizations', 'chief_position', $this->string(250));
        $this->addColumn('organizations', 'resp_department', $this->integer());
        $this->addColumn('organizations', 'UNOM', $this->string(11));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('organizations', 'public_service_available');
        $this->dropColumn('organizations', 'chief_name');
        $this->dropColumn('organizations', 'chief_position');
        $this->dropColumn('organizations', 'resp_department');
    }
}
