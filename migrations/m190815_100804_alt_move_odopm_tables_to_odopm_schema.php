<?php

use app\commands\migrate\Migration;

/**
 * Class m190815_100804_alt_move_odopm_tables_to_odopm_schema
 */
class m190815_100804_alt_move_odopm_tables_to_odopm_schema extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("ALTER TABLE odopm_areas SET SCHEMA odopm");
        $this->execute("ALTER TABLE odopm_districts SET SCHEMA odopm");
        $this->execute("ALTER TABLE odopm_organizations SET SCHEMA odopm");
        $this->execute("ALTER TABLE odopm_catalogs SET SCHEMA odopm");
        $this->execute("ALTER TABLE odopm_config SET SCHEMA odopm");
        $this->execute("ALTER TABLE odopm_catalogs_item SET SCHEMA odopm");
        $this->execute("ALTER TABLE odopm_attributes_specification SET SCHEMA odopm");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("ALTER TABLE odopm.odopm_areas SET SCHEMA public");
        $this->execute("ALTER TABLE odopm.odopm_districts SET SCHEMA public");
        $this->execute("ALTER TABLE odopm.odopm_organizations SET SCHEMA public");
        $this->execute("ALTER TABLE odopm.odopm_catalogs SET SCHEMA public");
        $this->execute("ALTER TABLE odopm.odopm_config SET SCHEMA public");
        $this->execute("ALTER TABLE odopm.odopm_catalogs_item SET SCHEMA public");
        $this->execute("ALTER TABLE odopm.odopm_attributes_specification SET SCHEMA public");
    }

}
