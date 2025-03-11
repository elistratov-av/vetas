<?php

use app\commands\migrate\Migration;
use app\models\db\FiasAddresses;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * Class m191030_111644_fix_old_fias_addresses
 */
class m191030_111644_fix_old_fias_addresses extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->lockMigrations();

        /**
         * Инициализируем ФИАС
         */
        $fias = require \Yii::getAlias('@app/config/fias.php');
        /** @var \app\common\components\address\FiasRemoteAdapter $addrSrv */
        $addrSrv = \Yii::createObject(['class' => 'app\common\components\AddressService',
            'adapter' => [
                'class' => 'app\common\components\address\FiasRemoteAdapter',
                'host' => $fias['host'],
                'port' => $fias['port'],
            ]
        ]);
        if ($addrSrv->getHi() !== ['Hi']) {
            Console::output('Cant connect to addr service ' . $fias['host'] . ':' . $fias['port']);
            return false;
        }

        /**
         * Выборка битых адресов
         */
        $query = FiasAddresses::find()
            ->where([
                'AND',
                ['NOT', ['houseguid' => null]],
                [
                    'OR',
                    ['ILIKE', 'house', '%стр%', false],
                    ['ILIKE', 'house', '%корп%', false],
                    ['ILIKE', 'house', '%лит%', false],
                    ['ILIKE', 'house', '%,%', false],
                ]
            ]);

        Console::output('Count: ' . $query->count());

        $this->setHouses($addrSrv, $query);

        $this->unlockMigrations();
    }

    /**
     * @param \app\common\components\address\FiasRemoteAdapter $addrSrv
     * @param Query $query
     */
    private function setHouses($addrSrv, $query)
    {
        foreach ($query->batch() as $rows) {

            /*
             * Для запроса
             */
            $rows = ArrayHelper::index($rows, 'houseguid');
            $guids = array_keys($rows);

            /*
             * Запрос
             */
            $result = $addrSrv->getHouseByGuid($guids);
            if (empty($result) || empty($result['result'])) {
                Console::error('[NOT FOUND]: ' . PHP_EOL . implode(PHP_EOL, $guids));
                continue;
            }
            $result = ArrayHelper::index($result['result'], 'id');

            /*
             * Обновляем
             */
            foreach ($rows as $key => $row) {
                /** @var FiasAddresses $row */
                if (!array_key_exists($key, $result) || empty($result[$key]['text'])) {
                    Console::error('[NOT FOUND] (' . $row->houseguid . ') ' . $row->house);
                    continue;
                }

                $new_text = $result[$key]['text'];
                if ($new_text === $row->house) {
                    Console::output("[=] ({$row->houseguid}) {$row->getOldAttribute('house')}");
                    continue;
                }
                $row->house = $result[$key]['text'];

                if ($row->save()) {
                    Console::output("[OK] ({$row->houseguid}) {$row->getOldAttribute('house')}  => {$row->house}");
                } else {
                    Console::error('[SAVE ERROR] (' . $row->houseguid . ') ' . $row->house);
                }
            }
        }
    }


    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m191030_111644_fix_old_fias_addresses cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191030_111644_fix_old_fias_addresses cannot be reverted.\n";

        return false;
    }
    */
}
