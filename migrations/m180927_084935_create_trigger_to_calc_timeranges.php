<?php

use app\commands\migrate\Migration;

/**
 * Class m180927_084935_create_trigger_to_calc_timeranges
 */
class m180927_084935_create_trigger_to_calc_timeranges extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('visits', 'time_range_without_cooldown', 'tsrange');
        $this->execute('ALTER TABLE public.visits ALTER COLUMN time_range DROP NOT NULL;');
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
  cooldown = make_interval(mins => NEW.cooldown);
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

        $sql = <<<SQL
create trigger calculate_visits_time_ranges_trigger
  before insert or update of status,channel,start_dttm,duration,fact_start_dttm,fact_end_dttm
 on public.visits
  for each row execute procedure calculate_visits_time_ranges();
SQL;
        $this->execute($sql);

        $this->execute('update visits
set status = vs.status
  from visits as vs
  where vs.id = visits.id;');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180927_084935_create_trigger_to_calc_timeranges cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180927_084935_create_trigger_to_calc_timeranges cannot be reverted.\n";

        return false;
    }
    */
}
