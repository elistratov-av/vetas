<?php

use app\commands\migrate\Migration;

/**
 * Class m190508_103258_fix_calculate_visits_time_ranges_trigger_for_mos_ru_2
 */
class m190508_103258_fix_calculate_visits_time_ranges_trigger_for_mos_ru_2 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
create or replace function calculate_visits_time_ranges()
  returns trigger
language plpgsql
as \$outer\$
declare
  start_dttm      timestamp;
  fact_start_dttm timestamp;
  duration        interval;
  cooldown        interval;
  tr              tsrange;
  tr_wc           tsrange;
  fc_tr           tsrange;
  fc_tr_wc        tsrange;
begin
  create or replace function check_tr_params(sd char, dr char, fact_sd char, fed char,  row_c visits)
    returns boolean
  language plpgsql
  as \$inner\$
  begin
    if sd = '+' and row_c.start_dttm is NULL
    then return false; END IF;
    if sd = '-' and row_c.start_dttm is NOT NULL
    then return false; END IF;
    if dr = '+' and row_c.duration is NULL
    then return false; END IF;
    if dr = '-' and row_c.duration is NOT NULL
    then return false; END IF;
    if fact_sd = '+' and row_c.fact_start_dttm is NULL
    then return false; END IF;
    if fact_sd = '-' and row_c.fact_start_dttm is NOT NULL
    then return false; END IF;
    if fed = '+' and row_c.fact_end_dttm is NULL
    then return false; END IF;
    if fed = '-' and row_c.fact_end_dttm is NOT NULL
    then return false; END IF;
    return true;
  end;
  \$inner\$;
  start_dttm = NEW.start_dttm;
  fact_start_dttm = NEW.fact_start_dttm;
  
  -- mos ru не трогаем при проведении приема или переносе
  IF NEW.channel = 2 AND (NEW.status = 'C' OR  NEW.status = 'W') THEN
    duration = make_interval(mins => OLD.duration);
    cooldown = make_interval(mins => COALESCE(OLD.cooldown, 0));
  ELSE
    duration = make_interval(mins => NEW.duration);
    cooldown = make_interval(mins => COALESCE(NEW.cooldown, 0));
  END IF;

  tr = tsrange(start_dttm, start_dttm + duration + cooldown, '[)' :: text);
  tr_wc = tsrange(start_dttm, start_dttm + duration, '[)' :: text);
  fc_tr = tsrange(fact_start_dttm, fact_start_dttm + duration + cooldown, '[)' :: text);
  fc_tr_wc = tsrange(fact_start_dttm, fact_start_dttm + duration, '[)' :: text);
  


  case NEW.channel      
    when 4
    then
      -- канал живой очереди
      case
        when NEW.status = 'N' and check_tr_params('-', '+', '-', '-', NEW)
        then
          NEW.time_range = null;
          NEW.time_range_without_cooldown = null; return NEW;
        when NEW.status = 'C' and check_tr_params('-', '+', '-', '-', NEW)
        then
          NEW.time_range = null;
          NEW.time_range_without_cooldown = null; return NEW;
        when NEW.status = 'W' and check_tr_params('-', '+', '+', '-', NEW)
        then
          NEW.time_range = fc_tr;
          NEW.time_range_without_cooldown = fc_tr_wc; return NEW;
        when NEW.status = 'А' and check_tr_params('-', '+', '-', '-', NEW)
        then
          NEW.time_range = null;
          NEW.time_range_without_cooldown = null; return NEW;
        when NEW.status = 'F' and check_tr_params('-', '+', '+', '+', NEW)
        then
          NEW.time_range = fc_tr;
          NEW.time_range_without_cooldown = fc_tr_wc; return NEW;
      else return NEW;
      end case;
  else
    -- остальные каналы
    case
      when NEW.status = 'N' and check_tr_params('+', '+', '-', '-', NEW)
      then
        NEW.time_range = tr;
        NEW.time_range_without_cooldown = tr_wc; return NEW;
      when NEW.status = 'C' and check_tr_params('+', '+', '-', '-', NEW)
      then
        NEW.time_range = tr;
        NEW.time_range_without_cooldown = tr_wc; return NEW;
      when NEW.status = 'Т' and check_tr_params('+', '+', '-', '-', NEW)
      then
        NEW.time_range = null;
        NEW.time_range_without_cooldown = null; return NEW;
      when NEW.status = 'W' and check_tr_params('+', '+', '+', '-', NEW)
      then
        NEW.time_range = fc_tr;
        NEW.time_range_without_cooldown = fc_tr_wc; return NEW;
      when NEW.status = 'А' and check_tr_params('+', '+', '-', '-', NEW)
      then
        NEW.time_range = null;
        NEW.time_range_without_cooldown = null; return NEW;
      when NEW.status = 'F' and check_tr_params('+', '+', '+', '+', NEW)
      then
        NEW.time_range = fc_tr;
        NEW.time_range_without_cooldown = fc_tr_wc; return NEW;
    else return NEW;
    end case;
    return NEW;
  end case;
  return NEW;
end;
\$outer\$;
SQL;

        $this->execute($sql);

        $this->execute('DROP trigger calculate_visits_time_ranges_trigger on public.visits');
        $sql = <<<SQL
create trigger calculate_visits_time_ranges_trigger
  before insert or update of cooldown,status,channel,start_dttm,duration,fact_start_dttm,fact_end_dttm
 on public.visits
  for each row execute procedure calculate_visits_time_ranges();
SQL;
        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $sql = <<<SQL
create or replace function calculate_visits_time_ranges()
  returns trigger
language plpgsql
as \$outer\$
declare
  start_dttm      timestamp;
  fact_start_dttm timestamp;
  duration        interval;
  cooldown        interval;
  tr              tsrange;
  tr_wc           tsrange;
  fc_tr           tsrange;
  fc_tr_wc        tsrange;
begin
  create or replace function check_tr_params(sd char, dr char, fact_sd char, fed char,  row_c visits)
    returns boolean
  language plpgsql
  as \$inner\$
  begin
    if sd = '+' and row_c.start_dttm is NULL
    then return false; END IF;
    if sd = '-' and row_c.start_dttm is NOT NULL
    then return false; END IF;
    if dr = '+' and row_c.duration is NULL
    then return false; END IF;
    if dr = '-' and row_c.duration is NOT NULL
    then return false; END IF;
    if fact_sd = '+' and row_c.fact_start_dttm is NULL
    then return false; END IF;
    if fact_sd = '-' and row_c.fact_start_dttm is NOT NULL
    then return false; END IF;
    if fed = '+' and row_c.fact_end_dttm is NULL
    then return false; END IF;
    if fed = '-' and row_c.fact_end_dttm is NOT NULL
    then return false; END IF;
    return true;
  end;
  \$inner\$;
  start_dttm = NEW.start_dttm;
  fact_start_dttm = NEW.fact_start_dttm;
  
  duration = make_interval(mins => NEW.duration);
  cooldown = make_interval(mins => COALESCE(NEW.cooldown, 0));
  tr = tsrange(start_dttm, start_dttm + duration + cooldown, '[)' :: text);
  tr_wc = tsrange(start_dttm, start_dttm + duration, '[)' :: text);
  fc_tr = tsrange(fact_start_dttm, fact_start_dttm + duration + cooldown, '[)' :: text);
  fc_tr_wc = tsrange(fact_start_dttm, fact_start_dttm + duration, '[)' :: text);
  case NEW.channel
    when 4
    then
      -- канал живой очереди
      case
        when NEW.status = 'N' and check_tr_params('-', '+', '-', '-', NEW)
        then
          NEW.time_range = null;
          NEW.time_range_without_cooldown = null; return NEW;
        when NEW.status = 'C' and check_tr_params('-', '+', '-', '-', NEW)
        then
          NEW.time_range = null;
          NEW.time_range_without_cooldown = null; return NEW;
        when NEW.status = 'W' and check_tr_params('-', '+', '+', '-', NEW)
        then
          NEW.time_range = fc_tr;
          NEW.time_range_without_cooldown = fc_tr_wc; return NEW;
        when NEW.status = 'А' and check_tr_params('-', '+', '-', '-', NEW)
        then
          NEW.time_range = null;
          NEW.time_range_without_cooldown = null; return NEW;
        when NEW.status = 'F' and check_tr_params('-', '+', '+', '+', NEW)
        then
          NEW.time_range = fc_tr;
          NEW.time_range_without_cooldown = fc_tr_wc; return NEW;
      else return NEW;
      end case;
  else
    -- остальные каналы
    case
      when NEW.status = 'N' and check_tr_params('+', '+', '-', '-', NEW)
      then
        NEW.time_range = tr;
        NEW.time_range_without_cooldown = tr_wc; return NEW;
      when NEW.status = 'C' and check_tr_params('+', '+', '-', '-', NEW)
      then
        NEW.time_range = tr;
        NEW.time_range_without_cooldown = tr_wc; return NEW;
      when NEW.status = 'Т' and check_tr_params('+', '+', '-', '-', NEW)
      then
        NEW.time_range = null;
        NEW.time_range_without_cooldown = null; return NEW;
      when NEW.status = 'W' and check_tr_params('+', '+', '+', '-', NEW)
      then
        NEW.time_range = fc_tr;
        NEW.time_range_without_cooldown = fc_tr_wc; return NEW;
      when NEW.status = 'А' and check_tr_params('+', '+', '-', '-', NEW)
      then
        NEW.time_range = null;
        NEW.time_range_without_cooldown = null; return NEW;
      when NEW.status = 'F' and check_tr_params('+', '+', '+', '+', NEW)
      then
        NEW.time_range = fc_tr;
        NEW.time_range_without_cooldown = fc_tr_wc; return NEW;
    else return NEW;
    end case;
    return NEW;
  end case;
  return NEW;
end;
\$outer\$;
SQL;

        $this->execute($sql);

        $this->execute('DROP trigger calculate_visits_time_ranges_trigger on public.visits');
        $sql = <<<SQL
create trigger calculate_visits_time_ranges_trigger
  before insert or update of cooldown,status,channel,start_dttm,duration,fact_start_dttm,fact_end_dttm
 on public.visits
  for each row execute procedure calculate_visits_time_ranges();
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
        echo "m190508_103258_fix_calculate_visits_time_ranges_trigger_for_mos_ru_2 cannot be reverted.\n";

        return false;
    }
    */
}
