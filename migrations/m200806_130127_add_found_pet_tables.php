<?php

use app\commands\migrate\Migration;

/**
 * Class m200806_130127_add_found_pet_tables
 */
class m200806_130127_add_found_pet_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('CREATE SCHEMA found_pet');

        $this->createTable('found_pet.ads', [
            'id' => $this->primaryKey(),
            'service_number' => $this->string(),
            'type' => $this->char(1)->comment('F - found (данные об обнаруженных), L - lost (данные о потерянных)'),
            'date_event' => $this->date()->comment('Дата нахождения / пропажи животного'),
            'time_event' => $this->time(0)->comment('Время нахождения / пропажи животного'),
            'id_address' => $this->integer()->comment('Адрес нахождения / пропажи животного'),
            'animal_name' => $this->string(),
            'chip' => $this->string(15),
            'stamp' => $this->string()->comment('Клеймо или татуировка'),
            'id_species' => $this->integer(),
            'id_breed' => $this->integer(),
            'id_color' => $this->integer(),
            'age' => $this->string(),
            'sex' => $this->char(1),
            'notice' => $this->string()->comment('Дополнительная информация'),
            'is_active' => $this->boolean()->comment('true - объявление активно, false - объявление в архиве'),
            'verify_status' => $this->boolean()->comment('null - На модерации, true - Одобрено модератором, false - Закрыто модератором'),
            'verify_at' => $this->dateTime(0),
            'closed_at' => $this->dateTime(0),
            'closed_reason' => $this->string(),
            'id_author' => $this->integer(),
            'photo' => $this->json()->comment('Массив идентификаторов фотографии ЦХЕД'),
            'stamp_photo' => $this->string()->comment('Фотография клейма животного. Идентификатор фотографии ЦХЕД'),
            'subscriptions' => $this->json()->comment('Подписки на рассылку по объявлению'),
            'created_at' => $this->dateTime(0),
            'updated_at' => $this->dateTime(0),
        ]);


        $this->createTable('found_pet.ad_addresses', [
            'id' => $this->primaryKey(),
            'city' => $this->string(),
            'region' => $this->string(),
            'settlement' => $this->string(),
            'area' => $this->string(),
            'street' => $this->string(),
            'house' => $this->string(),
            'postcode' => $this->string(),
            'geo_lat' => $this->string(),
            'geo_lon' => $this->string(),
            'radius' => $this->integer()->comment('Радиус поиска'),
            'fias_id' => $this->string(),
            'kladr_id' => $this->string(),
            'is_map' => $this->boolean()->comment('true - адрес задан по координатам карты, false -  адрес задан вручную'),
            'is_manually_set' => $this->boolean()->comment('true - адрес введен вручную, false -  адрес задан автоматически'),
            'pobox' => $this->string(500)->comment('Полный адрес, city + settlement + area + street'),
            'created_at' => $this->dateTime(0),
            'updated_at' => $this->dateTime(0)
        ]);

        $this->createTable('found_pet.ad_authors', [
            'id' => $this->primaryKey(),
            'sso_id' => 'uuid',
            'first_name' => $this->string(),
            'middle_name' => $this->string(),
            'last_name' => $this->string(),
            'phone' => $this->string(),
            'email' => $this->string(),
            'created_at' => $this->dateTime(0),
            'updated_at' => $this->dateTime(0)
        ]);

        $this->createIndex('idx_ads_type', 'found_pet.ads', 'type');
        $this->createIndex('idx_ads_is_active', 'found_pet.ads', 'is_active');
        $this->createIndex('idx_ads_verify_status', 'found_pet.ads', 'verify_status');

        $this->addForeignKey(
            'fk_ads_id_address',
            'found_pet.ads',
            'id_address',
            'found_pet.ad_addresses',
            'id',
            'SET NULL',
            'NO ACTION'
        );

        $this->addForeignKey(
            'fk_ads_id_species',
            'found_pet.ads',
            'id_species',
            'species',
            'id',
            'SET NULL',
            'NO ACTION'
        );

        $this->addForeignKey(
            'fk_ads_id_breed',
            'found_pet.ads',
            'id_breed',
            'breeds',
            'id',
            'SET NULL',
            'NO ACTION'
        );

        $this->addForeignKey(
            'fk_ads_id_author',
            'found_pet.ads',
            'id_author',
            'found_pet.ad_authors',
            'id',
            'CASCADE',
            'NO ACTION'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_ads_id_address', 'found_pet.ads');
        $this->dropForeignKey('fk_ads_id_species', 'found_pet.ads');
        $this->dropForeignKey('fk_ads_id_breed', 'found_pet.ads');
        $this->dropForeignKey('fk_ads_id_author', 'found_pet.ads');

        $this->dropTable('found_pet.ads');
        $this->dropTable('found_pet.ad_addresses');
        $this->dropTable('found_pet.ad_authors');
        $this->execute('DROP SCHEMA found_pet');
    }
}
