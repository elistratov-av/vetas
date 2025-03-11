<?php

use yii\db\Migration;

/**
 * Class m180712_115445_refactor_service_types_description_types_table
 */
class m180712_115445_refactor_service_types_description_types_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropPrimaryKey('service_types_description_types_pkey', 'service_types_description_types');
        $this->addColumn('service_types_description_types', 'id', $this->primaryKey());
        $this->createIndex(
            'service_types_description_types_unique',
            'service_types_description_types',
            ['id_description_type', 'id_service_type'],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropPrimaryKey('service_types_description_types_pkey', 'service_types_description_types');
        $this->dropColumn('service_types_description_types', 'id');
        $this->addPrimaryKey(
            'service_types_description_types_pkey',
            'service_types_description_types',
            ['id_description_type', 'id_service_type']
        );
        $this->dropIndex('service_types_description_types_unique', 'service_types_description_types');
    }
}
