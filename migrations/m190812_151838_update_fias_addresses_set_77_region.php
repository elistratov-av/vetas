<?php

use app\commands\migrate\Migration;
use app\models\db\FiasAddresses;
use yii\helpers\Console;

/**
 * Class m190812_151838_update_fias_addresses_set_77_region
 */
class m190812_151838_update_fias_addresses_set_77_region extends Migration
{

    /**
     * После добавления regionguid в fias_addresses у старых записей
     * он остался null. Это мешает фронту, тк сбрасывает автоселект дефолтной Москвы
     * Решили, в виду недостатка времени, для записей с 0c5b2444-70a0-4932-980c-b4dc0d3f02b5
     * вписать 77 регион, если иное не выставлено
     */

    const MOSCOW_UUID = '0c5b2444-70a0-4932-980c-b4dc0d3f02b5';
    const MOSCOW_REGION = 77;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        Console::output();
        Console::output();
        Console::output(Console::ansiFormat(
            'THIS MIGRATION USE CONSTANTS:',
            [Console::FG_YELLOW]
        ));

        Console::output(Console::ansiFormat(
            'Moscow cityguid = ' . self::MOSCOW_UUID,
            [Console::FG_YELLOW]
        ));

        Console::output(Console::ansiFormat(
            'Moscow regionguid = ' . self::MOSCOW_REGION,
            [Console::FG_YELLOW]
        ));

        Console::output();
        Console::output();

        $rows_count = FiasAddresses::updateAll([
            'regionguid' => self::MOSCOW_REGION
        ], [
            'cityguid' => self::MOSCOW_UUID,
            'regionguid' => null
        ]);


        Console::output(Console::ansiFormat(
            'ROWS UPDATED : ' . $rows_count,
            [Console::FG_GREEN]
        ));

        Console::output();
        Console::output();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190812_151838_update_fias_addresses_set_77_region cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190812_151838_update_fias_addresses_set_77_region cannot be reverted.\n";

        return false;
    }
    */
}
