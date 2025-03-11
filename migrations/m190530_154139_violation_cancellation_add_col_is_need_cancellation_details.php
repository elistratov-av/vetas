<?php

use app\commands\migrate\Migration;

/**
 * Class m190530_154139_violation_cancellation_add_col_is_need_cancellation_details
 */
class m190530_154139_violation_cancellation_add_col_is_need_cancellation_details extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            'violation_cancellation',
            'is_need_cancellation_details',
            $this->boolean()->notNull()->defaultValue('false')
        );

        $this->addCommentOnColumn(
            'violation_cancellation',
            'is_need_cancellation_details',
            'Флаг: при указании данного типа, необходимо заполнить поле violation.cancellation_details'
        );

        $this->update(
            'violation_cancellation',
            ['is_need_cancellation_details' => true],
            ['description' => 'иное']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(
            'violation_cancellation',
            'is_need_cancellation_details'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190530_154139_violation_cancellation_add_col_is_need_cancellation_details cannot be reverted.\n";

        return false;
    }
    */
}
