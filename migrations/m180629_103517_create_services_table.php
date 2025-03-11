<?php

use yii\db\Migration;

/**
 * Handles the creation of table `services`.
 */
class m180629_103517_create_services_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('services', [
            'id' => $this->primaryKey(),
            'id_pricelist' => $this->integer()->notNull()->comment('Ссылка на прейскурант'),
            'name' => $this->string(100)->notNull(),
            'price' => $this->decimal(8, 2)->notNull(),
            'sort_by' => $this->integer()
        ]);
        $this->addCommentOnTable('services', 'Таблица услуг');

        $this->createIndex(
            'idx-services-name_pricelist',
            'services',
            ['id_pricelist', 'name'],
            true
        );

        $this->createIndex(
            'idx-services-sort_by',
            'services',
            ['sort_by'],
            false
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-services-name_pricelist', 'services');
        $this->dropIndex('idx-services-sort_by', 'services');
        $this->dropTable('services');
    }
}
