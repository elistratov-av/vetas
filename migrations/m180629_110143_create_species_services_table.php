<?php

use yii\db\Migration;

/**
 * Handles the creation of table `species_services`.
 */
class m180629_110143_create_species_services_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('species_services', [
            'id' => $this->primaryKey(),
            'id_species' => $this->integer()->notNull()->comment('Ссылка на вид животного'),
            'id_service' => $this->integer()->notNull()->comment('Ссылка на услугу')
        ]);
        $this->addCommentOnTable('species_services', 'Услуги для видов животных');

        $this->addForeignKey(
            'fk-species_services-id_species',
            'species_services',
            'id_species',
            'species',
            'id',
            'NO ACTION'
        );

        $this->createIndex(
            'idx-species_services-unique',
            'species_services',
            ['id_species', 'id_service'],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-species_services-unique', 'species_services');
        $this->dropForeignKey('fk-species_services-id_species', 'species_services');
        $this->dropTable('species_services');
    }
}
