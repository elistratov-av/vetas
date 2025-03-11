<?php

use app\commands\migrate\Migration;

/**
 * Class m200712_173210_mosru_optimize_indices
 */
class m200712_173210_mosru_optimize_indices extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('create index if not exists "idx_visits_start_dttm" on public.visits (start_dttm)');
        $this->execute('create index if not exists "idx_shifts_id_type" on shifts (id_type)');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->execute('drop index if exists "idx_visits_start_dttm"');
        $this->execute('drop index if exists "idx_shifts_id_type"');
    }
}
