<?php

use app\commands\migrate\Migration;
use yii\db\Query;

/**
 * Class m210323_105959_3296_tmc_balance_flow_trigger
 */
class m210323_105959_3296_tmc_balance_flow_trigger extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->create_calc_and_update_balance_row();
        $this->execute("
            COMMENT ON FUNCTION 
            tmc.calc_and_update_balance_row(int) IS 'Ф-ция обновления строки баланса tmc.balance';
        ");

        $this->create_calc_balance_after_change_balance_flow();
        $this->execute("
            COMMENT ON FUNCTION 
            tmc.calc_balance_after_change_balance_flow() 
            IS 'Триггерная ф-ция обновления баланса после вставки строки в tmc.balance_flow';
        ");

        $sql = <<<SQL
CREATE TRIGGER calc_balance_after_change_balance_flow_trigger
    AFTER INSERT OR UPDATE OR DELETE
    ON tmc.balance_flow
    FOR EACH ROW
    EXECUTE PROCEDURE tmc.calc_balance_after_change_balance_flow();
SQL;
        $this->execute($sql);

        /*
         * Пересчитаем существующие
         */
        $query = (new Query())
            ->select('id')
            ->from('tmc.balance')
            ->where([
                'IN', 'type_tmc', ['vaccine', 'drug', 'exp_material']
            ]);

        foreach ($query->each(1) as $row) {
           $this->execute('SELECT tmc.calc_and_update_balance_row(:id);', [ 'id' => $row['id']]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("DROP TRIGGER calc_balance_after_change_balance_flow_trigger ON tmc.balance_flow");
        $this->execute('DROP FUNCTION tmc.calc_balance_after_change_balance_flow()');
        $this->execute('DROP FUNCTION tmc.calc_and_update_balance_row(int)');
    }

    protected function create_calc_and_update_balance_row()
    {
        $sql = <<<SQL
CREATE OR REPLACE FUNCTION tmc.calc_and_update_balance_row(balance_row_ID int)
    RETURNS VOID AS
    $$
DECLARE
    currentCount numeric;
    productionFormVolume numeric;
    lockedBalanceRows integer;
    balanceRow record;
BEGIN

    --- Лочим строку баланса
    SELECT * INTO balanceRow FROM tmc.balance WHERE id = balance_row_ID FOR UPDATE;

    --- Если такой строки нет - ОШИБКА
    GET DIAGNOSTICS lockedBalanceRows = ROW_COUNT;
    IF lockedBalanceRows <> 1 THEN
        RAISE EXCEPTION
            'Balance id % not found in tmc.balance', balance_row_ID
            USING ERRCODE = 'BAL11';
    END IF;

    IF balanceRow.type_tmc NOT IN ('drug', 'exp_material', 'vaccine') THEN
        RAISE EXCEPTION
            'Unknown type_tmc in row %. Accept only: drug, exp_material, vaccine', balance_row_ID
            USING ERRCODE = 'BAL12';
    END IF;

    --- Считаем кол-во (в стандартных единицах исчисления)
    SELECT
            ((SELECT COALESCE(SUM(count),0)
             FROM tmc.balance_flow
             WHERE id_tmc_balance = balance_row_ID
               AND flow_type = 'I'
                )
            -
            (SELECT COALESCE(SUM(count),0)
             FROM tmc.balance_flow
             WHERE id_tmc_balance = balance_row_ID
               AND flow_type IN ('D', 'H')
                )
        ) AS calc
    INTO currentCount;

    --- Объем формы выпуска
    SELECT COALESCE(production_form.volume, 1)
    INTO productionFormVolume
    FROM tmc.balance
             LEFT JOIN tmc.production_form ON
            balance.id_production_form = production_form.id
            AND balance.id_tmc = production_form.id_tmc
            AND balance.type_tmc = production_form.type_tmc
    WHERE balance.id = balance_row_ID;

    EXECUTE 'UPDATE tmc.balance  SET count =  $1, count_in_production_form = $2 WHERE id = $3;'
    USING
        currentCount, (currentCount / productionFormVolume), balance_row_ID;
END;
$$
    Language plpgsql;

SQL;

        $this->execute($sql);

    }

    protected function create_calc_balance_after_change_balance_flow()
    {
        $sql = <<<SQL
CREATE OR REPLACE FUNCTION tmc.calc_balance_after_change_balance_flow()
RETURNS TRIGGER AS $$
DECLARE
  
  sqlPartCalcCount text;
  row_ID integer;
  currentCount decimal;
BEGIN
    ---
    --- Триггерная ф-ция обновления балансов
    ---

	--- Запрещаем обновление
	IF TG_OP = 'UPDATE' THEN
		RAISE EXCEPTION 'Use INSERT or DELETE'
        USING HINT = 'This table accept INSERT or DELETE',
              ERRCODE = 'BAL03';
	END IF;
	
	
	--- Валидация для новых
	IF TG_OP = 'INSERT' THEN
        --- Проверяем приход/расход 
        IF NEW.flow_type NOT IN ('I', 'D', 'H') THEN
          RAISE EXCEPTION  
            'Unknown flow_type. Accept only (I)increase, (D)decrease, (H)hold'
          USING ERRCODE = 'BAL02';
        END IF;
    END IF;
    
    --- Переменные для удобства
    IF TG_OP = 'INSERT' THEN
      row_ID = NEW.id_tmc_balance;
    ELSEIF TG_OP = 'DELETE' THEN
      row_ID = OLD.id_tmc_balance;
    END IF;

    
    --- Высчитываем текущее значение и обновляем баланс 
   PERFORM tmc.calc_and_update_balance_row(row_ID);
    
    --- Вставка строки | удаление
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
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210323_105959_3296_tmc_balance_flow_trigger cannot be reverted.\n";

        return false;
    }
    */
}
