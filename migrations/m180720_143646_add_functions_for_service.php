<?php

use yii\db\Migration;

/**
 * Class m180720_143646_add_functions_for_service
 */
class m180720_143646_add_functions_for_service extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
CREATE OR REPLACE FUNCTION public.service_sign(
    cabinet_exists boolean,
    specialist_exists boolean,
    balance_exists boolean)
  RETURNS boolean AS
\$BODY$
begin
    return cabinet_exists AND specialist_exists AND balance_exists;
end;
\$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
SQL;

        $this->execute($sql);

        $sql = <<<SQL
CREATE OR REPLACE FUNCTION public.service_reason(
    cabinet_exists boolean,
    specialist_exists boolean,
    balance_exists boolean,
    cabinet_name text,
    specialist_name text,
    balance_name text)
  RETURNS text AS
\$BODY$
declare
    result text;
begin
    CASE 
        WHEN NOT cabinet_exists THEN result := 'Отсутствует необходимый кабинет: ' || cabinet_name;
        WHEN NOT specialist_exists THEN result := 'Отсутствует специалисты: ' || specialist_name;
        WHEN NOT balance_exists THEN result := 'Отсутствует ТМЦ: ' || balance_name;
        ELSE result := '';
    END CASE;

    RETURN result;
end;
\$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
SQL;

        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("DROP FUNCTION public.service_sign(boolean, boolean, boolean)");
        $this->execute("DROP FUNCTION public.service_reason(boolean, boolean, boolean, text, text, text)");
    }
}
