<?php

use yii\db\Migration;

/**
 * Class m180810_085746_create_visits_service_types_description_view
 */
class m180810_085746_create_visits_service_types_description_view extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE OR REPLACE VIEW public.visits_available_description_types AS 
 SELECT description_types.id,
    description_types.name,
    description_types.entity_type,
    visits_gov_services.id_visit
   FROM visits_gov_services
     JOIN gov_services ON gov_services.id = visits_gov_services.id_service
     JOIN service_types ON gov_services.id_service_type = service_types.id
     LEFT JOIN service_types_description_types ON service_types_description_types.id_service_type = service_types.id
     JOIN description_types ON description_types.id = service_types_description_types.id_description_type
  WHERE description_types.entity_type::text = 'visit'::text;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON VIEW public.visits_available_description_types
  IS 'Представление для вывода доступных для визита типов описаний через связи визит->услуги->типы услуг->типы описаний'");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('DROP VIEW public.visits_available_description_types');
    }

}
