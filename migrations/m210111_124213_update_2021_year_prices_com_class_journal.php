<?php

use app\commands\migrate\Migration;

/**
 * Class m210111_124213_update_2021_year_prices_com_class_journal
 */
class m210111_124213_update_2021_year_prices_com_class_journal extends Migration
{
    private const CODES = ['0284', '0285', '0421', '0428'];

    private static $table = 'public.gov_services';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update(self::$table, ['com_class_journal' => 'additional'], [
            'and',
            ['deleted' => false],
            ['in', 'cod', self::CODES],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->update(self::$table, ['com_class_journal' => null], [
            'and',
            ['deleted' => false],
            ['in', 'cod', self::CODES],
        ]);
    }
}
