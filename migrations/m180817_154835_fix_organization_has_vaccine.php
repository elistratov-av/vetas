<?php

use yii\db\Migration;

/**
 * Class m180817_154835_fix_organization_has_vaccine
 */
class m180817_154835_fix_organization_has_vaccine extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE OR REPLACE FUNCTION public.organization_has_vaccine(integer)
  RETURNS boolean AS
'SELECT EXISTS (SELECT 1 FROM balance_vaccines WHERE id_organization = $1 AND dose_count > 0);'
  LANGUAGE sql VOLATILE
  COST 100;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON FUNCTION public.organization_has_vaccine(integer) IS 'Функциля для определения наличия на балансе организации любых вакцин'");

        $sql = <<<SQL
CREATE OR REPLACE FUNCTION public.organization_has_vaccine(
    integer,
    integer)
  RETURNS boolean AS
'SELECT EXISTS (SELECT 1 FROM balance_vaccines WHERE id_organization = $1 AND id_vaccine = $2 AND dose_count > 0);'
  LANGUAGE sql VOLATILE
  COST 100;
SQL;
        $this->execute($sql);
        $this->execute("COMMENT ON FUNCTION public.organization_has_vaccine(integer, integer) IS 'Функциля для определения наличия на балансе организации определенной вакцины'");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180817_154835_fix_organization_has_vaccine cannot be reverted.\n";

        return true;
    }
}
