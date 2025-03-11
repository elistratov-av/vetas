<?php

use app\commands\migrate\Migration;

/**
 * Class m191022_094639_2418_fill_table_password_history
 */
class m191022_094639_2418_fill_table_password_history extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = <<<SQL
insert into public.password_history (id_user, target, password, created_at) (select id, 1, password, NOW() from public.users where is_temp_password = false order by id asc);
SQL;

        $this->execute($sql);

        $sql = <<<SQL
insert into public.password_history (id_user, target, password, created_at) (select id, 2, password, NOW() from admin.users_fstek where is_temp_password = false order by id asc);
SQL;

        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->truncateTable('public.password_history');
    }
}
