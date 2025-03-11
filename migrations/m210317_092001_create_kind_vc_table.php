<?php

use app\commands\migrate\Migration;

/**
 * Class m210317_092001_create_kind_vc_table
 */
class m210317_092001_create_kind_vc_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('public.kind_vc', [
            'id' => $this->bigPrimaryKey(),
            'name' => $this->string()->notNull(),
            'description' => $this->text(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);

        $this->addForeignKey(
            'KIND_VC_CREATED_BY',
            'public.kind_vc',
            'created_by',
            'public.users',
            'id');
        $this->addForeignKey(
            'KIND_VC_UPDATED_BY',
            'public.kind_vc',
            'updated_by',
            'public.users',
            'id');

        $this->batchInsert('public.kind_vc', [
            'name',
        ], [
            [
                'Стационарный',
            ],
            [
                'Передвижной',
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('public.kind_vc');
    }
}
