<?php

use yii\db\Migration;

/**
 * Class m180817_150613_change_organization_balance_for_service
 */
class m180817_150613_change_organization_balance_for_service extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE OR REPLACE FUNCTION public.organization_balance_for_service(
    IN organization_id integer,
    IN service_id integer)
  RETURNS TABLE(id_tmc_type integer, service_tmc_class character varying, name character varying, tmc_class tmc_class_list, available boolean) AS
\$BODY$
begin
    return query
        select 
            service_tmcs.id_tmc_type, service_tmcs.tmc_class, tmc_types.name, tmc_types.tmc_class,
            case 
                when tmc_types.id is not null then organization_on_balance(tmc_types.tmc_class::text, $1, tmc_types.id)
                else organization_on_balance(service_tmcs.tmc_class, $1)
            end
        from service_tmcs
        left join tmc_types on tmc_types.id = service_tmcs.id_tmc_type
        where service_tmcs.id_service = $2 AND service_tmcs.is_required;
end;
\$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
SQL;

        $this->execute("DROP FUNCTION public.organization_balance_for_service(integer, integer)");
        $this->execute($sql);
        $this->execute("COMMENT ON FUNCTION public.organization_balance_for_service(integer, integer) IS 'Функциля для вывода ТМЦ необходимых для услуги, с определением их наличия у организации'");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $sql = <<<SQL
CREATE OR REPLACE FUNCTION public.organization_balance_for_service(
    IN organization_id integer,
    IN service_id integer)
  RETURNS TABLE(id_tmc integer, service_tmc_class character varying, name character varying, tmc_class tmc_class_list, available boolean) AS
\$BODY$
begin
    return query
        select 
            service_tmcs.id_tmc, service_tmcs.tmc_class, tmc.name, tmc_types.tmc_class,
            case 
                when tmc.id is not null then organization_on_balance(tmc_types.tmc_class::text, $1, tmc.id)
                else organization_on_balance(service_tmcs.tmc_class, $1)
            end
        from service_tmcs
        left join tmc on tmc.id = service_tmcs.id_tmc
        left join tmc_types on tmc_types.id = tmc.id_tmc_type
        where service_tmcs.id_service = $2 AND service_tmcs.is_required;
end;
\$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100
  ROWS 1000;
SQL;

        $this->execute("DROP FUNCTION public.organization_balance_for_service(integer, integer)");
        $this->execute($sql);
        $this->execute("COMMENT ON FUNCTION public.organization_balance_for_service(integer, integer) IS 'Функциля для вывода ТМЦ необходимых для услуги, с определением их наличия у организации'");
    }
}
