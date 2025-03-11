<?php

use app\commands\migrate\Migration;
use yii\db\Query;
use yii\helpers\Console;

/**
 * Class m190723_112101_set_areas_for_fias_addresses
 */
class m190723_112101_set_areas_for_fias_addresses extends Migration
{
    /**
     * {@inheritdoc}
     * @throws \yii\base\InvalidConfigException
     */
    public function safeUp()
    {
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

        $this->setHouses($addrSrv);
        $this->setStreets($addrSrv);


    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190723_112101_set_areas_for_fias_addresses cannot be reverted.\n";

        return false;
    }

    /**
     * @param \app\common\components\address\FiasRemoteAdapter $addrSrv
     */
    private function setHouses($addrSrv): void
    {
        $query = (new Query())->from('fias_addresses')
            ->andWhere(['not', ['houseguid' => null]])
            ->andWhere(['oktmo' => null]);

        foreach ($query->batch() as $rows) {

            $q = [
                'q' => array_map(function ($x) {
                    return $x['houseguid'];
                }, $rows)
            ];

            $res = $addrSrv->postBatchHouse($q);
            /** Пропускаем что не нашли */
            if (empty($res)){
                continue;
            }

            $res = array_filter($res, function ($x) {
                return $x['oktmo'] != null;
            });

            foreach ($res as $item) {
                $this->update('fias_addresses', ['oktmo' => $item['oktmo']], ['houseguid' => $item['aoguid']]);
            }
        }
    }

    /**
     * @param \app\common\components\address\FiasRemoteAdapter $addrSrv
     */
    private function setStreets($addrSrv): void
    {
        $query = (new Query())->from('fias_addresses')
            ->andWhere(['not', ['streetguid' => null]])
            ->andWhere(['oktmo' => null]);

        foreach ($query->batch() as $rows) {

            $q = [
                'q' => array_map(function ($x) {
                    return $x['streetguid'];
                }, $rows)
            ];

            $res = $addrSrv->postBatchStreet($q);

            $res = array_filter($res, function ($x) {
                return $x['oktmo'] != null;
            });

            foreach ($res as $item) {
                $this->update('fias_addresses', ['oktmo' => $item['oktmo']], ['streetguid' => $item['aoguid']]);
            }
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190723_112101_set_areas_for_fias_addresses cannot be reverted.\n";

        return false;
    }
    */
}
