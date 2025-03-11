<?php

use app\commands\migrate\Migration;

/**
 * Class m190424_144914_balance_flow_allow_delete
 */
class m190424_144914_balance_flow_allow_delete extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute("DROP TRIGGER calc_balance_after_change_balance_flow_trigger ON balance_flow");
        $this->execute('DROP FUNCTION calc_balance_after_change_balance_flow()');

        $sql = <<<SQL
CREATE OR REPLACE FUNCTION calc_balance_after_change_balance_flow()
RETURNS TRIGGER AS $$
DECLARE
  balanceTableName text;
  balanceColumnName text;
  lockedBalanceRows integer;
  sqlPartCalcCount text;
  row_ID integer;
  row_TYPE text;
BEGIN
    ---
    --- Триггерная ф-ция обновления балансов
    ---

	--- Запрещаем обновление
	IF TG_OP = 'UPDATE' THEN
		RAISE EXCEPTION 'Use INSERT or DELETE'
        USING HINT = 'This table accept INSERT or DELETE',
              ERRCODE = 'BAL03'
		  ;
	END IF;
	
	
	--- Валидация для новых
	IF TG_OP = 'INSERT' THEN
        --- Проверяем приход/расход 
        IF NEW.flow_type <> 'income' AND NEW.flow_type <> 'expense' THEN
          RAISE EXCEPTION  
            'Unknown flow_type. Accept only income or expense'
          USING ERRCODE = 'BAL02';
        END IF;
    END IF;
    
    --- Переменные для удобства
    IF TG_OP = 'INSERT' THEN
      row_ID = NEW.id_balance_tmc_type;
      row_TYPE = NEW.balance_tmc_type;
    ELSEIF TG_OP = 'DELETE' THEN
      row_ID = OLD.id_balance_tmc_type;
      row_TYPE = OLD.balance_tmc_type;
    END IF;

     --- Разбираемся, в какой таблице балансов выполнять пересчет
    CASE
        WHEN row_TYPE = 'drug' THEN 
            balanceTableName = 'balance_drugs';
            balanceColumnName = 'dose_count';
        WHEN row_TYPE = 'exp_material' THEN 
            balanceTableName = 'balance_exp_materials';
            balanceColumnName = 'count';
        WHEN row_TYPE = 'vaccine' THEN 
            balanceTableName = 'balance_vaccines';
            balanceColumnName = 'dose_count';
        ELSE
            RAISE EXCEPTION  'Unknown balance_tmc_type. Accept only (drug, exp_material, vaccine)';
    END CASE;

    ---
      

    --- Лочим строку баланса 
	EXECUTE 'SELECT * FROM ' || balanceTableName  || ' WHERE id = ' || row_ID || ' FOR UPDATE;';

	--- Если такой строки нет - ОШИБКА
    GET DIAGNOSTICS lockedBalanceRows = ROW_COUNT;
	IF lockedBalanceRows <> 1 THEN 
      RAISE EXCEPTION 
        'Balance id % not found in %', row_ID, balanceTableName
      USING ERRCODE = 'BAL03';
    END IF;

    --- Высчитываем текущее значение и обновляем баланс 
    sqlPartCalcCount = '
 (SELECT
    (SELECT COALESCE(SUM(count),0)  
     FROM balance_flow
     WHERE id_balance_tmc_type = ' || row_ID || '
       AND flow_type = $1) 
  -
    (SELECT COALESCE(SUM(count),0)
     FROM balance_flow
     WHERE id_balance_tmc_type = ' || row_ID || '
       AND flow_type = $2) AS result
 ) AS calc';

    EXECUTE 'UPDATE ' 
            || balanceTableName  || ' SET '  || balanceColumnName || ' = calc.result ' ||
            'FROM ' || sqlPartCalcCount || '
            WHERE id = ' || row_ID || '; '
     USING 
       'income', 'expense'
     ;
    
    --- Вставка строки| удаление
    IF TG_OP = 'INSERT' THEN
      RETURN NEW;
    ELSEIF TG_OP = 'DELETE' THEN
      RETURN OLD;
    END IF;
    
END;
$$
LANGUAGE plpgsql;
SQL;

        $this->execute($sql);
        $this->execute("COMMENT ON FUNCTION calc_balance_after_change_balance_flow() IS 'Триггерная ф-ция обновления баланса после вставки строки в balance_flow';");

        $sql = <<<SQL
CREATE TRIGGER calc_balance_after_change_balance_flow_trigger
    AFTER INSERT OR UPDATE OR DELETE
    ON balance_flow
    FOR EACH ROW
    EXECUTE PROCEDURE calc_balance_after_change_balance_flow();
SQL;
        $this->execute($sql);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("DROP TRIGGER calc_balance_after_change_balance_flow_trigger ON balance_flow");
        $this->execute('DROP FUNCTION calc_balance_after_change_balance_flow()');

        $sql = <<<SQL
CREATE OR REPLACE FUNCTION calc_balance_after_change_balance_flow()
RETURNS TRIGGER AS $$
DECLARE
  balanceTableName text;
  balanceColumnName text;
  lockedBalanceRows integer;
  sqlPartCalcCount text;
BEGIN
    ---
    --- Триггерная ф-ция обновления балансов
    ---

	--- Запрещаем обновление
	IF TG_OP = 'UPDATE' OR TG_OP = 'DELETE' THEN
		RAISE EXCEPTION 'Use INSERT'
        USING HINT = 'This table accept only INSERT',
              ERRCODE = 'BAL03'
		  ;
	END IF;
	
	--- Проверяем приход/расход 
    IF NEW.flow_type <> 'income' AND NEW.flow_type <> 'expense' THEN
      RAISE EXCEPTION  
        'Unknown flow_type. Accept only income or expense'
      USING ERRCODE = 'BAL02';
    END IF;

	--- Разбираемся, в какой таблице балансов выполнять пересчет
	CASE
		WHEN NEW.balance_tmc_type = 'drug' THEN 
			balanceTableName = 'balance_drugs';
			balanceColumnName = 'dose_count';
		WHEN NEW.balance_tmc_type = 'exp_material' THEN 
			balanceTableName = 'balance_exp_materials';
			balanceColumnName = 'count';
		WHEN NEW.balance_tmc_type = 'vaccine' THEN 
			balanceTableName = 'balance_vaccines';
			balanceColumnName = 'dose_count';
		ELSE
			RAISE EXCEPTION  'Unknown balance_tmc_type. Accept only (drug, exp_material, vaccine)';
    END CASE;


    --- Лочим строку баланса 
	EXECUTE 'SELECT * FROM ' || balanceTableName  || ' WHERE id = ' || NEW.id_balance_tmc_type || ' FOR UPDATE;';

	--- Если такой строки нет - ОШИБКА
    GET DIAGNOSTICS lockedBalanceRows = ROW_COUNT;
	IF lockedBalanceRows <> 1 THEN 
      RAISE EXCEPTION 
        'Balance id % not found in %', NEW.id_balance_tmc_type, balanceTableName
      USING ERRCODE = 'BAL03';
    END IF;

    --- Высчитываем текущее значение и обновляем баланс 
    sqlPartCalcCount = '
 (SELECT
    (SELECT COALESCE(SUM(count),0)  
     FROM balance_flow
     WHERE id_balance_tmc_type = ' || NEW.id_balance_tmc_type || '
       AND flow_type = $1) 
  -
    (SELECT COALESCE(SUM(count),0)
     FROM balance_flow
     WHERE id_balance_tmc_type = ' || NEW.id_balance_tmc_type || '
       AND flow_type = $2) AS result
 ) AS calc';

    EXECUTE 'UPDATE ' 
            || balanceTableName  || ' SET '  || balanceColumnName || ' = calc.result ' ||
            'FROM ' || sqlPartCalcCount || '
            WHERE id = ' || NEW.id_balance_tmc_type || '; '
     USING 
       'income', 'expense'
     ;
    
    --- Вставка строки
    RETURN NEW;
END;
$$
LANGUAGE plpgsql;
SQL;

        $this->execute($sql);
        $this->execute("COMMENT ON FUNCTION calc_balance_after_change_balance_flow() IS 'Триггерная ф-ция обновления баланса после вставки строки в balance_flow';");

        $sql = <<<SQL
CREATE TRIGGER calc_balance_after_change_balance_flow_trigger
    AFTER INSERT OR UPDATE OR DELETE
    ON balance_flow
    FOR EACH ROW
    EXECUTE PROCEDURE calc_balance_after_change_balance_flow();
SQL;
        $this->execute($sql);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190424_144914_balance_flow_allow_delete cannot be reverted.\n";

        return false;
    }
    */
}
