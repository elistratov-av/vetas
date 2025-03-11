<?php

use yii\db\Migration;

/**
 * Handles the creation of table `visit_description_types`.
 */
class m180711_135448_create_visits_description_types_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE OR REPLACE VIEW public.visits_description_types AS 
 SELECT self.id_visit,
    self.id_service,
    service_desc_types.id_description_type
   FROM visits_gov_services self
     JOIN gov_services service ON service.id = self.id_service
     JOIN service_types_description_types service_desc_types ON service_desc_types.id_service_type = service.id_service_type
     JOIN description_types desc_type ON desc_type.id = service_desc_types.id_description_type;
SQL;

        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("DROP VIEW public.visits_description_types;");
    }
}
