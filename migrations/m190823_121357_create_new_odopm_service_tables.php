<?php

use app\commands\migrate\Migration;

/**
 * Class m190823_121357_create_new_odopm_service_tables
 */
class m190823_121357_create_new_odopm_service_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('odopm.organizations_actions', [
            'id' => $this->primaryKey(),
            'id_organization' => $this->integer(),
            'dttm_action' => $this->string()->comment('время совершения действия'),
            'entry_add_reason' => $this->integer()->comment('айдишник причины из справочника'),
            'entry_change_reason' => $this->string(500)->comment('причина изменения'),
            'entry_deleted_reason' => $this->integer()->comment('айдишник причины из справочника'),
        ]);

        $this->createTable('odopm.organizations_relationship', [
            'id' => $this->integer(),
            'id_action' => $this->integer(),
            'id_organization' => $this->integer(),
            'parent_entries_organization' => $this->integer()->comment('Код записи в каталоге ОДОПМ, соответствующей организации  parent_entries_organization'),
            'child_entries_organization' => $this->integer()->comment('содержит идентификатор организацию organizations.id_organization'),
            'child_entries' => $this->integer()->comment('Код записи в каталоге ОДОПМ (odopm_catalogs_item.id_item), соответствующей организации  child_entries_organization')
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('odopm.organizations_actions');
        $this->dropTable('odopm.organizations_relationship');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190823_121357_create_new_odopm_service_tables cannot be reverted.\n";

        return false;
    }
    */
}
