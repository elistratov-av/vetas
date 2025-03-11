<?php

use app\commands\migrate\Migration;

/**
 * Class m210317_092003_add_vc_fields_to_organizations_table
 */
class m210317_092003_add_vc_fields_to_organizations_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('public.organizations', 'number_vc', $this->string());
        $this->addColumn('public.organizations', 'data_vc', $this->date());
        $this->addColumn('public.organizations', 'id_kind', $this->bigInteger());
        $this->addColumn('public.organizations', 'id_reason', $this->bigInteger());

        $this->addForeignKey(
            'ORGANIZATIONS_ID_KIND',
            'public.organizations',
            'id_kind',
            'public.kind_vc',
            'id');
        $this->addForeignKey(
            'ORGANIZATIONS_ID_REASON',
            'public.organizations',
            'id_reason',
            'public.reason_vc',
            'id');

        $this->insert('public.org_types', [
            'name' => 'Прививочный пункт',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('ORGANIZATIONS_ID_KIND', 'public.organizations');
        $this->dropForeignKey('ORGANIZATIONS_ID_REASON', 'public.organizations');

        $this->dropColumn('public.organizations', 'number_vc');
        $this->dropColumn('public.organizations', 'data_vc');
        $this->dropColumn('public.organizations', 'id_kind');
        $this->dropColumn('public.organizations', 'id_reason');
    }
}
