<?php

use app\commands\migrate\Migration;

/**
 * Class m190220_110357_mosru_specialists_view
 */
class m190220_110357_mosru_specialists_view extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE OR REPLACE VIEW mosru.specialists AS 
 SELECT DISTINCT specialists.id_organization,
    specialists.id as id_specialist,
    users.id as id_user,
    users.fullname AS name
   FROM specialists
     JOIN services_specialists ON specialists.id = services_specialists.id_specialist AND specialists.id_organization = services_specialists.id_organization
     JOIN users ON users.id = specialists.id_user 
  WHERE specialists.expel_date > now() OR specialists.expel_date IS NULL
  ORDER BY users.fullname;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON VIEW mosru.specialists IS 'Специалисты оказывающие услуги для mos.ru'");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("DROP VIEW mosru.specialists");
    }
}
