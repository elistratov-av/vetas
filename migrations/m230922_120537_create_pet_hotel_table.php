<?php

use yii\db\Migration;

/**
 * Handles the creation of table `pet_hotel`.
 */
class m230922_120537_create_pet_hotel_table extends Migration
{
    const PET_HOTEL_TABLE = 'pet_hotel';
    const ORGANIZATIONS_TABLE = 'organizations';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(self::PET_HOTEL_TABLE, [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'id_organization' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->timestamp(0),
            'updated_at' => $this->timestamp(0),
        ]);

        $this->addForeignKey(
            'fk_pet_hotel-id_organization',
            self::PET_HOTEL_TABLE,
            'id_organization',
            self::ORGANIZATIONS_TABLE,
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(self::PET_HOTEL_TABLE);
    }
}
