<?php

use app\commands\migrate\Migration;

/**
 * Class m190117_103944_update_reg_certificates_add_column_to_update
 */
class m190117_103944_update_reg_certificates_add_column_to_update extends Migration
{
    private $tableName = 'public.reg_certificates';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            '{{%' . $this->tableName . '}}',
            'to_update',
            $this->boolean()
                ->defaultValue(false)
                ->comment('Нужно ли перегенерировать сертификат в связи с изменением данных владельца или животного')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%' . $this->tableName . '}}', 'to_update');
    }
}
