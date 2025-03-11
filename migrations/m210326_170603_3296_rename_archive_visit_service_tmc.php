<?php

use app\commands\migrate\Migration;

/**
 * Class m210326_170603_3296_rename_archive_visit_service_tmc
 */
class m210326_170603_3296_rename_archive_visit_service_tmc extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('ALTER TABLE public.visit_service_tmc RENAME TO visit_service_tmc_archive');
        $this->addCommentOnTable(
            'public.visit_service_tmc_archive',
            'Архивные данные об использованных ТМЦ до введения раздельных балансов. '.
            'Используется для отдачи в старых приемах'

        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('ALTER TABLE public.visit_service_tmc_archive RENAME TO visit_service_tmc');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210326_170603_3296_rename_archive_visit_service_tmc cannot be reverted.\n";

        return false;
    }
    */
}
