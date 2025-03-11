<?php

use app\commands\migrate\Migration;

/**
 * Class m190304_122231_change_gov_services
 */
class m190304_122231_change_gov_services extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("DROP VIEW etp.visits_mosru_services");
        $this->execute("DROP VIEW mosru.services");

        $this->addColumn('gov_services', 'at_clinic', $this->boolean()->defaultValue(true));
        $this->addColumn('gov_services', 'deleted', $this->boolean()->defaultValue(false));
        $this->alterColumn('gov_services', 'cod', $this->string(20));
        $this->addCommentOnColumn('gov_services', 'at_clinic', 'Признак: услуга оказывается в клинике');
        $this->addCommentOnColumn('gov_services', 'deleted', 'Признак: услуга удалена');

        $this->createIndex(
            'idx-gov_services-deleted',
            'gov_services',
            'deleted'
        );

        $sql = <<<SQL
CREATE OR REPLACE VIEW mosru.services AS
 SELECT 
    *
  FROM gov_services
  WHERE type = 'mosru'
  ORDER BY id;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON VIEW mosru.services IS 'Услуги mos.ru'");

        $sql = <<<SQL
CREATE OR REPLACE VIEW etp.visits_mosru_services AS 
 SELECT visits_gov_services.id_visit,
    mosru.services.id,
    mosru.services.name
   FROM visits_gov_services
     LEFT JOIN mosru.services ON mosru.services.id = visits_gov_services.id_service;
SQL;
        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-gov_services-deleted', 'gov_services');
        $this->dropColumn('gov_services', 'at_clinic');
        $this->dropColumn('gov_services', 'deleted');
        $this->alterColumn('gov_services', 'cod', $this->char(4));
    }

}
