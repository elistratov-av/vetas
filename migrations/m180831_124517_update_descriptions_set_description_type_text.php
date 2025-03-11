<?php

use app\commands\migrate\Migration;

/**
 * Class m180831_124517_update_descriptions_set_description_type_text
 */
class m180831_124517_update_descriptions_set_description_type_text extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // В этой миграции изменяется тип descriptions.description на text
        
        // сначала удаляются связанные вьюхи
        $this->execute("DROP VIEW public.vaccines_description_types");
        $this->execute("DROP VIEW public.drugs_description_types");
        
        // изменяется тип
        $this->execute('ALTER TABLE "descriptions" ALTER COLUMN "description" TYPE text USING "description"::text');
        
        // далее создаются вьюхи
        
        // команда из m180803_152840_create_vaccines_description_types_view
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
        
        // команда из m180615_125338_drugs_view
        $this->execute("
            CREATE OR REPLACE VIEW public.drugs_description_types AS 
                SELECT descriptions.*, description_types.name, description_types.entity_type FROM descriptions
                JOIN description_types ON description_types.id = descriptions.id_description_type
                WHERE description_types.entity_type::text = 'drug'::text
        ");
        // команда из m180706_083757_add_delete_rules_to_views
        $this->execute("
CREATE RULE drugs_description_types_delete AS ON DELETE TO drugs_description_types
    DO INSTEAD
    DELETE FROM descriptions
     WHERE id = OLD.id;        
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180831_124517_update_descriptions_set_description_type_text cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180831_124517_update_descriptions_set_description_type_text cannot be reverted.\n";

        return false;
    }
    */
}
