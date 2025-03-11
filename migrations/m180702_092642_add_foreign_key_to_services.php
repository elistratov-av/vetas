<?php

use yii\db\Migration;

/**
 * Class m180702_092642_add_foreign_key_to_services
 */
class m180702_092642_add_foreign_key_to_services extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->truncateTable('services');
        $this->addForeignKey(
            'fk-services-id_pricelist',
            'services',
            'id_pricelist',
            'pricelists',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-services-id_pricelist', 'services');
    }

}
