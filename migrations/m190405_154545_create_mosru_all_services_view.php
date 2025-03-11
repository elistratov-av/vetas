<?php

use app\commands\migrate\Migration;

/**
 * Class m190405_154545_create_mosru_all_services_view
 */
class m190405_154545_create_mosru_all_services_view extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE OR REPLACE VIEW mosru.services_all AS 
 SELECT mosru_services.id,
    mosru_services.name,
    'old'::text AS type
   FROM mosru_services
UNION
 SELECT gov_services.id,
    gov_services.name,
    'new'::text AS type
   FROM gov_services
  WHERE gov_services.type::text = 'mosru'::text
  ORDER BY 1;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON VIEW mosru.services_all
  IS 'Представление для вывода вывода услуг mos.ru из старой таблицы и новой (для обратной совместимости методов SOAP)'");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("DROP VIEW mosru.services_all");
    }

}
