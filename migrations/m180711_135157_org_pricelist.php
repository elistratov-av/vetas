<?php

use yii\db\Migration;

/**
 * Class m180711_135157_org_pricelist
 */
class m180711_135157_org_pricelist extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('organizations', 'id_pricelist', 'integer');
        $this->addColumn('gov_services', 'id_service_measure', 'integer');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180711_135157_org_pricelist cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180711_135157_org_pricelist cannot be reverted.\n";

        return false;
    }
    */
}
