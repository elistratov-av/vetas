<?php

use yii\db\Migration;

/**
 * Class m180706_083757_add_delete_rules_to_views
 */
class m180706_083757_add_delete_rules_to_views extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("
CREATE RULE drugs_description_types_delete AS ON DELETE TO drugs_description_types
    DO INSTEAD
    DELETE FROM descriptions
     WHERE id = OLD.id;        
        ");

        $this->execute("
CREATE RULE organizations_cabinet_types_delete AS ON DELETE TO organizations_cabinet_types
    DO INSTEAD
    DELETE FROM organization_cabinets
     WHERE id = OLD.id;        
        ");

        $this->execute("
CREATE RULE specialists_specializations_delete AS ON DELETE TO specialists_specializations
    DO INSTEAD
    DELETE FROM personal_specializations
     WHERE id = OLD.id;        
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("DROP RULE drugs_description_types_delete ON drugs_description_types;");
        $this->execute("DROP RULE organizations_cabinet_types_delete ON organizations_cabinet_types;");
        $this->execute("DROP RULE specialists_specializations_delete ON specialists_specializations;");
    }

}
