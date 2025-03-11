<?php

use app\commands\migrate\Migration;

/**
 * Class m181207_110302_create_statistic_mos_ru_view
 */
class m181207_110302_create_statistic_mos_ru_view extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('CREATE OR REPLACE VIEW statistic.statistic_mos_ru AS
                        select o.short_name, t.created_at, t.status, t.c 
                        from (
                            select id_organization, status, created_at::date, count(*) as c 
                            from visits 
                            where id in (
                                select visit_id 
                                from etp.message 
                            ) 
                            group by id_organization, status, created_at::date
                        ) as t 
                        join organizations as o on o.id = t.id_organization order by o.short_name, t.created_at');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute("DROP VIEW statistic.statistic_mos_ru;");
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181207_110302_create_statistic_mos_ru_view cannot be reverted.\n";

        return false;
    }
    */
}
