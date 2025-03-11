<?php

use app\commands\migrate\Migration;

/**
 * Class m210317_092002_create_reason_vc_table
 */
class m210317_092002_create_reason_vc_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('public.reason_vc', [
            'id' => $this->bigPrimaryKey(),
            'name' => $this->string()->notNull(),
            'description' => $this->text(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);

        $this->addForeignKey(
            'REASON_VC_CREATED_BY',
            'public.reason_vc',
            'created_by',
            'public.users',
            'id');
        $this->addForeignKey(
            'REASON_VC_UPDATED_BY',
            'public.reason_vc',
            'updated_by',
            'public.users',
            'id');

        $this->batchInsert('public.reason_vc', [
            'name',
        ], [
            [
                'Плановый',
            ],
            [
                'Карантин',
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('public.reason_vc');
    }
}
