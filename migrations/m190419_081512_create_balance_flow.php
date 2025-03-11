<?php

use app\commands\migrate\Migration;

/**
 * Class m190419_081512_create_balance_flow
 */
class m190419_081512_create_balance_flow extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('balance_flow',[
            'id' => $this->bigPrimaryKey(),
            'id_organization' => $this->integer(),
            'balance_tmc_type' => 'tmc_class_list NOT NULL',
            'id_balance_tmc_type' => $this->integer(),
            'id_visitservice' => $this->integer(),
            'flow_type' => $this->string(32),
            'count' => $this->integer(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->timestamp(0),
            'updated_at' => $this->timestamp(0),
        ]);

        $this->createIndex(
            'idx_balance_flow_id_balance_tmc_type',
            'balance_flow',
            ['id_balance_tmc_type', 'flow_type']
        );

        $this->addForeignKey(
            'fk_balance_flow_organization',
            'balance_flow',
            'id_organization',
            'organizations',
            'id',
            'NO ACTION',
            'NO ACTION'
        );

        $this->addForeignKey(
            'fk_balance_flow_visits_gov_servicesn',
            'balance_flow',
            'id_visitservice',
            'visits_gov_services',
            'id',
            'NO ACTION',
            'NO ACTION'
        );

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

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

        $this->execute("DROP TRIGGER calc_balance_after_change_balance_flow_trigger ON balance_flow");
        $this->execute('DROP FUNCTION calc_balance_after_change_balance_flow()');

        $this->dropForeignKey(
            'fk_balance_flow_organization',
            'balance_flow'
        );

        $this->dropForeignKey(
            'fk_balance_flow_visits_gov_servicesn',
            'balance_flow'
        );

        $this->dropIndex(
            'idx_balance_flow_id_balance_tmc_type',
            'balance_flow'
        );
        $this->dropTable('balance_flow');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190419_081512_create_balance_flow cannot be reverted.\n";

        return false;
    }
    */
}
