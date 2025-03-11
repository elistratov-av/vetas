<?php

use yii\db\Migration;

/**
 * Handles the creation of table `user_feedback`.
 */
class m210430_081002_create_user_feedback_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('owner_feedback', [
            'id' => $this->primaryKey(),
            'id_violation' => $this->integer()->notNull()->comment('ID нарушения'),
            'planned_close_date' => $this->date()->comment('Запланированная дата закрытия нарушения'),
            'id_tmc' => $this->integer()->comment('ID вакцины'),
            'production_date' => $this->date()->comment('Дата изготовления вакцины'),
            'batch' => $this->string()->comment('Номер партии/серии'),
            'id_organization' => $this->integer()->comment('Организация в которой была произведена вакцинация'),
            'is_out_org' => $this->boolean()->comment('Организация вне справочника ВЕТИАС'),
            'vaccine_date' => $this->date()->comment('Дата произведённой вакцинации'),
            'expiry_date' => $this->date()->comment('Срок годности вакцины'),
            'valid_until' => $this->date()->comment('Вакцинация действительна до'),
            'id_ident_type' => $this->integer()->comment('Способ идентификации'),
            'identification_code' => $this->string()->comment('Идентификационный номер'),
            'is_processed' => $this->boolean()->notNull()->defaultValue(false)->comment('Запрос обработан инспектором'),
        ]);

        $this->createTable('outside_org', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()->unique()->comment('Наименование огранизации'),
        ]);

        $this->addColumn('violation', 'feedback_token', $this->string()->unique()->comment('Токен доступа к форме обратной связи по нарушению'));
        $this->addColumn('pet_other_vaccinations', 'is_out_org', $this->boolean()->defaultValue(false)->comment('Проведена организацией вне справочника ВЕТАИС'));
        $this->addColumn('pet_rabies_vaccination', 'is_out_org', $this->boolean()->defaultValue(false)->comment('Проведена организацией вне справочника ВЕТАИС'));

        $this->addForeignKey(
            'fk-owner_feedback-id_violation',
            'owner_feedback',
            'id_violation',
            'violation',
            'id_violation',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_owner_feedback-id_tmc',
            'owner_feedback',
            'id_tmc',
            'tmc.tmc',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_owner_feedback-id_ident_type',
            'owner_feedback',
            'id_ident_type',
            'identification_types',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-owner_feedback-id_organization',
            'owner_feedback',
            'id_organization',
            'organizations',
            'id',
            'CASCADE'
        );

        $this->db->createCommand('CREATE OR REPLACE VIEW org_dictionary AS
SELECT * from
(select *, false as is_out_org from organizations
union all
select 
id, 
Null as parent_id, 
Null as id_org_type, 
name, 
Null as short_name, 
Null as inn, 
Null as kpp, 
Null as ogrn, 
Null as id_address, 
Null as schedule, 
Null as id_area, 
Null as id_district, 
Null as created_by, 
Null as updated_by, 
Null as created_at, 
Null as updated_at, 
Null as reg_number, 
Null as id_fias_address, 
Null as mark_up_flag, 
Null as mark_up_ratio, 
Null as mark_up_from_time, 
Null as mark_up_to_time, 
false as reseption_corpses, 
false as free_vaccination, 
false as pet_registration, 
Null as latitude, 
Null as longitude, 
Null as comment, 
Null as clarification_schedule, 
Null as chief_name, 
Null as chief_position, 
Null as unom, 
Null as capital_structure, 
false as public_services_available,
true as is_out_org from outside_org) as foo')
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->db->createCommand('DROP VIEW org_dictionary')->execute();
        $this->dropTable('owner_feedback');
        $this->dropTable('outside_org');
        $this->dropColumn('violation', 'feedback_token');
        $this->dropColumn('pet_other_vaccinations', 'is_out_org');
        $this->dropColumn('pet_rabies_vaccination', 'is_out_org');
    }
}
