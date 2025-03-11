<?php

use yii\db\Migration;

/**
 * Class m180803_152840_create_vaccines_description_types_view
 */
class m180803_152840_create_vaccines_description_types_view extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE OR REPLACE VIEW public.vaccines_description_types AS 
 SELECT descriptions.id,
    descriptions.entity_id,
    descriptions.description,
    descriptions.id_description_type,
    description_types.name,
    description_types.entity_type
   FROM descriptions
     JOIN description_types ON description_types.id = descriptions.id_description_type
  WHERE description_types.entity_type::text = 'vaccine'::text;
SQL;
        $this->execute($sql);

        $sql = <<<SQL
CREATE OR REPLACE RULE vaccines_description_types_delete AS
    ON DELETE TO vaccines_description_types DO INSTEAD  DELETE FROM descriptions
  WHERE descriptions.id = old.id;
SQL;
        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("DROP VIEW public.vaccines_description_types");
    }

}
