<?php

use app\commands\migrate\Migration;

/**
 * Class m190424_122229_add_organisations_to_discount
 */
class m190429_122229_add_organisations_to_discount extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('discount', 'id_organization', $this->integer());

        $this->addForeignKey(
            'fk-discount-id_organization',
            'discount',
            'id_organization',
            'organizations',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190424_122229_add_organisations_to_discount cannot be reverted.\n";

        return false;
    }

}
