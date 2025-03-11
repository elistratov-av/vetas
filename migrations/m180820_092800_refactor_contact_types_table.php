<?php

use yii\db\Migration;
use app\models\db\ContactTypes;

/**
 * Handles adding type_column_entity_type to table `contact_types`.
 */
class m180820_092800_refactor_contact_types_table extends Migration
{
    protected $contactTypes = [
        ['Мобильный телефон', ContactTypes::TYPE_PHONE, 'pet_owner'],
        ['Рабочий телефон', ContactTypes::TYPE_PHONE, 'pet_owner'],
        ['Домашний телефон', ContactTypes::TYPE_PHONE, 'pet_owner'],
        ['Основной телефон', ContactTypes::TYPE_PHONE, 'pet_owner'],
        ['Факс', ContactTypes::TYPE_PHONE, 'pet_owner'],
        ['Электронная почта', ContactTypes::TYPE_EMAIL, 'pet_owner'],
        ['Телефон', ContactTypes::TYPE_PHONE, 'organization'],
        ['Телефон справочной', ContactTypes::TYPE_PHONE, 'organization'],
        ['Телефон регистратуры', ContactTypes::TYPE_PHONE, 'organization'],
        ['Телефон вызова выезной бригады', ContactTypes::TYPE_PHONE, 'organization'],
        ['Вызов врача на дом', ContactTypes::TYPE_PHONE, 'organization'],
        ['Телефон главврача', ContactTypes::TYPE_PHONE, 'organization'],
        ['Телефон приемной', ContactTypes::TYPE_PHONE, 'organization'],
        ['Телефон ветеринарной лаборатории', ContactTypes::TYPE_PHONE, 'organization'],
        ['Телефон для записи на прием', ContactTypes::TYPE_PHONE, 'organization'],
        ['Круглосуточный телефон', ContactTypes::TYPE_PHONE, 'organization'],
        ['Многоканальный телефон', ContactTypes::TYPE_PHONE, 'organization'],
        ['Круглосуточный многоканальный телефон', ContactTypes::TYPE_PHONE, 'organization'],
        ['Факс организации', ContactTypes::TYPE_PHONE, 'organization'],
        ['Электронная почта организации', ContactTypes::TYPE_EMAIL, 'organization'],
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("TRUNCATE TABLE contact_types CASCADE");
        $this->renameColumn('contact_types', 'contact_type', 'type');
        $this->alterColumn('contact_types', 'type', 'DROP NOT NULL');
        $this->addColumn('contact_types', 'entity_type', $this->string()->notNull());

        $this->createIndex(
            'idx_contact_types_name',
            'contact_types',
            'name',
            true
        );

        foreach ($this->contactTypes as $contactType) {
            $this->insert('contact_types', [
                'name' => $contactType[0],
                'type' => $contactType[1],
                'entity_type' => $contactType[2]
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameColumn('contact_types', 'type', 'contact_type');
        $this->alterColumn('contact_types', 'contact_type', $this->string(50));
        $this->alterColumn('contact_types', 'contact_type', 'SET NOT NULL');
        $this->dropColumn('contact_types', 'entity_type');

        $this->dropIndex('idx_contact_types_name', 'contact_types');
        $this->execute("TRUNCATE TABLE contact_types CASCADE");
    }
}
