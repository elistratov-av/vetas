<?php

use app\commands\migrate\Migration;

/**
 * Class m190308_203337_fix_failed_index_creation_for_users_table
 */
class m190308_203337_fix_failed_index_creation_for_users_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // попытка пофиксить создание индекса после изменений таблицы users
        // (07.03.2018 на dev миграция по добавлению новых полей \m190211_090733_update_table_users_add_personal_info_columns
        // упала и применилась только когда закомментировал создание индексов)

        $this->execute('CREATE INDEX IF NOT EXISTS "idx-users-fio" ON "users" ("f_fio", "i_fio", "o_fio")');
        $this->execute('CREATE INDEX IF NOT EXISTS "idx-users-photo" ON "users" ("photo")');

        $sql = <<<SQL
DO $$
BEGIN
    IF NOT EXISTS ( SELECT constraint_schema, constraint_name
                FROM information_schema.constraint_column_usage 
                WHERE constraint_schema = 'public'
                  AND constraint_name = 'fk-users-photo'
              )
    THEN
        ALTER TABLE "users" ADD CONSTRAINT "fk-users-photo" FOREIGN KEY ("photo") REFERENCES "files" ON DELETE SET NULL;
    END IF;
END$$; 
SQL;

        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('ALTER TABLE "users" DROP CONSTRAINT IF EXISTS "fk-users-photo"');
        $this->execute('DROP INDEX IF EXISTS "idx-users-fio"');
        $this->execute('DROP INDEX IF EXISTS "idx-users-photo"');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190308_203337_fix_failed_index_creation_for_users_table cannot be reverted.\n";

        return false;
    }
    */
}
