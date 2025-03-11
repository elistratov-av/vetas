<?php

use app\commands\migrate\Migration;

/**
 * Class m200324_074625_2656_add_trgm_support
 */
class m200324_074625_2656_add_trgm_support extends Migration
{
    private $tableName = 'public.pet_owners';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // сначала нужно установить расширение
        // $this->execute('create extension pg_trgm');

        $this->execute('create index "idx_' . 'pet_owners' . '_f_fio_gin" on ' . $this->tableName . ' using gin (f_fio gin_trgm_ops);');
        $this->execute('create index "idx_' . 'pet_owners' . '_i_fio_gin" on ' . $this->tableName . ' using gin (i_fio gin_trgm_ops);');
        $this->execute('create index "idx_' . 'pet_owners' . '_o_fio_gin" on ' . $this->tableName . ' using gin (o_fio gin_trgm_ops);');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200324_074625_2656_add_trgm_support cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200324_074625_2656_add_trgm_support cannot be reverted.\n";

        return false;
    }
    */
}
