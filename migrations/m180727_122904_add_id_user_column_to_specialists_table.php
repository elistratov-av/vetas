<?php

use yii\db\Migration;

/**
 * Handles adding id_user to table `specialists`.
 */
class m180727_122904_add_id_user_column_to_specialists_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('specialists', 'id_user', $this->integer());
        $this->addForeignKey(
            'fk-specialists-id_user',
            'specialists',
            'id_user',
            'users',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('specialists', 'id_user');
    }
}
