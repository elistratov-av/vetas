<?php

use app\commands\migrate\Migration;
use app\models\db\Organizations;

/**
 * Class m220214_074634_add_managing_organization_to_organizations_table
 */
class m220214_074634_add_managing_organization_to_organizations_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableName = Organizations::tableName();

        $this->addColumn($tableName, 'managing_organization_id', $this->integer());
        $this->addColumn($tableName, 'is_managing_organization', $this->boolean());

        $this->addForeignKey(
            'fk-organizations-managing_organization_id',
            $tableName,
            'managing_organization_id',
            $tableName,
            'id',
            'SET NULL', 'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $tableName = Organizations::tableName();

        $this->dropForeignKey('fk-organizations-managing_organization_id', $tableName);
        $this->dropColumn(Organizations::tableName(), 'managing_organization_id');
        $this->dropColumn(Organizations::tableName(), 'is_managing_organization');
    }
}
