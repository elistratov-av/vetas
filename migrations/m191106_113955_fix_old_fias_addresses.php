<?php

use app\commands\migrate\Migration;
use app\models\db\FiasAddresses;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
/**
 * Class m191106_113955_fix_old_fias_addresses
 */
class m191106_113955_fix_old_fias_addresses extends Migration
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
             * Запрос
             */
            $guids = array_unique(ArrayHelper::getColumn($rows, 'houseguid'));
            $result = $addrSrv->getHouseByGuid($guids);
            if (empty($result) || empty($result['result'])) {
                Console::error('[NOT FOUND]: ' . PHP_EOL . implode(PHP_EOL, $guids));
                continue;
            }
            $result = ArrayHelper::index($result['result'], 'id');

            /*
             * Обновляем
             */
            foreach ($rows as $row) {
                /** @var FiasAddresses $row */
                if (!array_key_exists($row->houseguid, $result) || empty($result[$row->houseguid]['text'])) {
                    Console::error('[NOT FOUND] (' . $row->houseguid . ') ' . $row->house);
                    continue;
                }

                $new_text = $result[$row->houseguid]['text'];
                if ($new_text == $row->house) {
                    Console::output("[=] ({$row->houseguid}) {$row->getOldAttribute('house')}");
                    continue;
                }
                $old = $row->house;
                $row->house = $new_text;

                if ($row->save()) {
                    Console::output("[OK] ({$row->houseguid}) '{$old}' => '{$row->house}'");
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
        echo "m191106_113955_fix_old_fias_addresses cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191106_113955_fix_old_fias_addresses cannot be reverted.\n";

        return false;
    }
    */
}
