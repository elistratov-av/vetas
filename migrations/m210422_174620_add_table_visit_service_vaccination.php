<?php

use app\commands\migrate\Migration;

/**
 * Class m210422_174620_add_table_visit_service_vaccination
 */
class m210422_174620_add_table_visit_service_vaccination extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('public.visit_service_vaccination', [
            'id'                   => $this->primaryKey(),
            'id_visit_service_tmc' => $this->bigInteger()->notNull()->comment('id услуги ТМЦ'),
            'batch'                => $this->string(255)->comment('Номер партии/серии'),
            'production_date'      => $this->date()->comment('Дата изготовления'),
            'expiry_date'          => $this->date()->comment('Срок годности'),
            'date'                 => $this->date()->notNull()->comment('Дата вакцинации'),
            'valid_until'          => $this->date()->comment('Действительно до')
        ]);

        $this->addCommentOnTable(
            'public.visit_service_vaccination',
            'Вакцинация в приеме (внебалансовые ТМЦ)'
        );

        $this->createIndex('idx-visit_service_vaccination-visit_service_tmc', 'public.visit_service_vaccination', 'id_visit_service_tmc', true);
        $this->addForeignKey(
            'fk-visit_service_vaccination-visit_service_tmc',
            'public.visit_service_vaccination',
            'id_visit_service_tmc',
            'visit_service_tmc',
            'id',
            'CASCADE',
            'CASCADE'
        );

        //pet_rabies_vaccination
        $this->createIndex('idx-pet_rabies_vaccination-visit_service_tmc', 'public.pet_rabies_vaccination', 'id_visit_service_tmc');
        //pet_other_vaccinations
        $this->createIndex('idx-pet_other_vaccinations-visit_service_tmc', 'public.pet_other_vaccinations', 'id_visit_service_tmc');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-visit_service_vaccination-visit_service_tmc', 'public.visit_service_vaccination');
        $this->dropTable('public.visit_service_vaccination');
        $this->dropIndex('idx-pet_rabies_vaccination-visit_service_tmc', 'public.pet_rabies_vaccination');
        $this->dropIndex('idx-pet_other_vaccinations-visit_service_tmc', 'public.pet_other_vaccinations');
    }
}
