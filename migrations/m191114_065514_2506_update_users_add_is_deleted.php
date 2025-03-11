<?php

use app\commands\migrate\Migration;

/**
 * Class m191114_065514_2506_update_users_add_is_deleted
 */
class m191114_065514_2506_update_users_add_is_deleted extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            'public.users',
            'is_deleted',
            $this->boolean()->notNull()->defaultValue(false)->comment('Флаг: пользователь удален'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('public.users', 'is_deleted');

    }
}
