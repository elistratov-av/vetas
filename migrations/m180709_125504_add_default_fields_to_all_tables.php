<?php

use yii\db\Migration;

/**
 * Class m180709_125504_add_default_fields_to_all_tables
 */
class m180709_125504_add_default_fields_to_all_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
do $$
declare
    selectrow record;
begin
for selectrow in
    select 
      'ALTER TABLE '|| T.mytable || ' ADD COLUMN  IF NOT EXISTS created_by integer Null; 
      ALTER TABLE '|| T.mytable || ' ADD COLUMN  IF NOT EXISTS updated_by integer Null; 
      ALTER TABLE '|| T.mytable || ' ADD COLUMN  IF NOT EXISTS created_at timestamp Null; 
      ALTER TABLE '|| T.mytable || ' ADD COLUMN  IF NOT EXISTS updated_at timestamp Null;
      COMMENT ON COLUMN '|| T.mytable || '.created_by IS ''Автор добавления (id пользователя)'';
      COMMENT ON COLUMN '|| T.mytable || '.updated_by IS ''Автор последнего изменения (id пользователя)'';
      COMMENT ON COLUMN '|| T.mytable || '.created_at IS ''Дата создания'';
      COMMENT ON COLUMN '|| T.mytable || '.updated_at IS ''Дата изменения'';
      ' as script 
   from 
      ( 
        select tablename as mytable from  pg_tables where schemaname  = 'public' and tablename <> 'migration'
      ) t
loop
execute selectrow.script;
end loop;
end;
$$;
SQL;

        $this->execute($sql);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $sql = <<<SQL
do $$
declare
    selectrow record;
begin
for selectrow in
    select 
      'ALTER TABLE '|| T.mytable || ' DROP COLUMN  IF EXISTS created_by; 
      ALTER TABLE '|| T.mytable || ' DROP COLUMN  IF EXISTS updated_by; 
      ALTER TABLE '|| T.mytable || ' DROP COLUMN  IF EXISTS created_at; 
      ALTER TABLE '|| T.mytable || ' DROP COLUMN  IF EXISTS updated_at;
      ' as script 
   from 
      ( 
        select tablename as mytable from  pg_tables where schemaname  = 'public' and tablename <> 'migration'
      ) t
loop
execute selectrow.script;
end loop;
end;
$$;
SQL;

        $this->execute($sql);
    }
}
