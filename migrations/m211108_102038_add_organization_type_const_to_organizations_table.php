<?php

use app\commands\migrate\Migration;

/**
 * Class m211108_102038_add_organization_type_const_to_organizations_table
 */
class m211108_102038_add_organization_type_const_to_organizations_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('organizations', 'organization_type_const', $this->string()->defaultValue(null)
                ->comment('Системные типы организаций, прописанные в константах класса OrganizationType. На эти константы завязан код приложения, поэтому они вынесены в отдельную колонку')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('organizations', 'organization_type_const');
    }
}
