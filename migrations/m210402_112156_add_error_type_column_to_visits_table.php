<?php

use yii\db\Migration;

/**
 * Handles adding error_type to table `visits`.
 */
class m210402_112156_add_error_type_column_to_visits_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('visits', 'error_type', $this->string()->null());
        $this->addCommentOnColumn('visits', 'error_type', 'Тип ошибки для алерта, если ошибка есть: I - отсутствует идентификация, V - отсутствует вакцинация, IV - отсутствует идентификация и вакцинация. Иначе null');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('visits', 'error_type');
    }
}
