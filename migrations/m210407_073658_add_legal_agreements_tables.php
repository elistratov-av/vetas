<?php

use app\commands\migrate\Migration;

/**
 * Добавляет таблицы public.agreement_types и public.agreements
 */
class m210407_073658_add_legal_agreements_tables extends Migration
{
    const AGGREMENT_TYPES = 'agreement_types';

    const AGGREMENTS = 'agreements';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Типы согласий
        $this->createTable('public.' . self::AGGREMENT_TYPES, [
            'id' => $this->smallInteger(),
            'name' => $this->string()->notNull()->unique(),
            'created_at' => $this->dateTime(0),
            'created_by' => $this->integer(),
            'updated_at' => $this->dateTime(0),
            'updated_by' => $this->integer(),
            // Предотвращаем добавление автоинкремента
            'PRIMARY KEY (id)'
        ]);

        // Согласия
        $this->createTable('public.' . self::AGGREMENTS, [
            'id' => $this->primaryKey(),
            'is_agree' => $this->boolean()->notNull(),
            'id_type' => $this->integer()->notNull(),
            'id_pet_owner' => $this->integer()->notNull(),
            'id_visit' => $this->integer()->notNull(),
            'id_pet' => $this->integer(),
            'id_organization' => $this->integer()->notNull(),
            'created_at' => $this->dateTime(0),
            'created_by' => $this->integer(),
            'updated_at' => $this->dateTime(0),
            'updated_by' => $this->integer(),
        ]);

        // FK: agreements -> agreement_types
        $this->addForeignKey(
            'fk-' . self::AGGREMENTS . '-'. self::AGGREMENT_TYPES,
            'public.' . self::AGGREMENTS,
            'id_type',
            'public.' . self::AGGREMENT_TYPES,
            'id',
            'NO ACTION',
            'CASCADE'
        );

        // FK: agreements -> pet_owners
        $reftbl = 'pet_owner';
        $this->addForeignKey(
            'fk-' . self::AGGREMENTS . '-pet_owners',
            'public.' . self::AGGREMENTS,
            'id_pet_owner',
            'public.pet_owners',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // FK: agreements -> visits
        $this->addForeignKey(
            'fk-' . self::AGGREMENTS . '-visits',
            'public.' . self::AGGREMENTS,
            'id_visit',
            'public.visits',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // FK: agreements -> pets
        $this->addForeignKey(
            'fk-' . self::AGGREMENTS . '-pets',
            'public.' . self::AGGREMENTS,
            'id_pet',
            'public.pets',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // FK: agreements -> organizations
        $this->addForeignKey(
            'fk-' . self::AGGREMENTS . '-organizations',
            'public.' . self::AGGREMENTS,
            'id_organization',
            'public.organizations',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Создаём типы согласия
        $this->batchInsert(
            self::AGGREMENT_TYPES,
            ['id', 'name'],
            [
                [1, 'согласие на обработку персональных данных'],
                [2, 'согласие на оперативное вмешательство'],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('public.agreements');
        $this->dropTable('public.agreement_types');
    }
}
