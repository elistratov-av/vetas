<?php

use app\commands\migrate\Migration;

/**
 * Class m210326_162651_3296_move_old_tmc_tables
 */
class m210326_162651_3296_move_old_tmc_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('CREATE SCHEMA IF NOT EXISTS tmc_archive');
        $this->execute("COMMENT ON SCHEMA tmc_archive IS 
        'Архив со старыми таблицами ТМЦ. Не используется в коде. По завершению сверки и тестирования - можно удалить'");

        $this->execute('ALTER TABLE public.balance_drugs SET SCHEMA tmc_archive;');
        $this->execute('ALTER TABLE public.balance_equipments SET SCHEMA tmc_archive;');
        $this->execute('ALTER TABLE public.balance_exp_materials SET SCHEMA tmc_archive;');
        $this->execute('ALTER TABLE public.balance_flow SET SCHEMA tmc_archive;');
        $this->execute('ALTER TABLE public.balance_vaccines SET SCHEMA tmc_archive;');
        $this->execute('ALTER TABLE public.drugs SET SCHEMA tmc_archive;');
        $this->execute('ALTER TABLE public.equipments SET SCHEMA tmc_archive;');
        $this->execute('ALTER TABLE public.exp_materials SET SCHEMA tmc_archive;');
        $this->execute('ALTER TABLE public.vaccines SET SCHEMA tmc_archive;');
        $this->execute('ALTER TABLE public.tmc SET SCHEMA tmc_archive;');

        $this->execute('ALTER VIEW public.v2_visit_tmc_balance SET SCHEMA tmc_archive;');
        $this->execute('ALTER VIEW public.v2_visit_tmc_list SET SCHEMA tmc_archive;');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('ALTER TABLE tmc_archive.balance_drugs SET SCHEMA public;');
        $this->execute('ALTER TABLE tmc_archive.balance_equipments SET SCHEMA public;');
        $this->execute('ALTER TABLE tmc_archive.balance_exp_materials SET SCHEMA public;');
        $this->execute('ALTER TABLE tmc_archive.balance_flow SET SCHEMA public;');
        $this->execute('ALTER TABLE tmc_archive.balance_vaccines SET SCHEMA public;');
        $this->execute('ALTER TABLE tmc_archive.drugs SET SCHEMA public;');
        $this->execute('ALTER TABLE tmc_archive.equipments SET SCHEMA public;');
        $this->execute('ALTER TABLE tmc_archive.exp_materials SET SCHEMA public;');
        $this->execute('ALTER TABLE tmc_archive.vaccines SET SCHEMA public;');
        $this->execute('ALTER TABLE tmc_archive.tmc SET SCHEMA public;');

        $this->execute('ALTER VIEW tmc_archive.v2_visit_tmc_balance SET SCHEMA public;');
        $this->execute('ALTER VIEW tmc_archive.v2_visit_tmc_list SET SCHEMA public;');

        $this->execute('DROP SCHEMA tmc_archive');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210326_162651_3296_move_old_tmc_tables cannot be reverted.\n";

        return false;
    }
    */
}
