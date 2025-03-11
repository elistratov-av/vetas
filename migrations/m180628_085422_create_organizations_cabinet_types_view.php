<?php

use yii\db\Migration;

/**
 * Class m180628_085422_create_organizations_cabinet_types_view
 */
class m180628_085422_create_organizations_cabinet_types_view extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("CREATE OR REPLACE VIEW public.organizations_cabinet_types AS SELECT * FROM organization_cabinets;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("DROP VIEW public.organizations_cabinet_types;");
    }


}
