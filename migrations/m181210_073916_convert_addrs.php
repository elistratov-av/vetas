<?php

use app\commands\migrate\Migration;
use app\models\db\FiasAddresses;
use app\models\db\Organizations;
use app\models\db\PetOwners;
use yii\db\Query;
use yii\helpers\Console;

/**
 * Class m181210_073916_convert_addrs
 */
class m181210_073916_convert_addrs extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function Up()
    {

        Console::output('Всего овнеров с адресами: ' . PetOwners::find()->andWhere('id_address is not null')->count());

        $time = time();
        $i = 0;

        foreach (PetOwners::find()->andWhere('id_address is not null')->each() as $owner) {
            $transaction = $this->db->beginTransaction();
            try {
                $addr = $owner->address->name;

                $address = $this->findAddressWithHouse($addr);

                $owner->id_fias_address = $address->id;
                $owner->save();


                $ans = $address ? ($address->streetguid) : 'нет';
                Console::clearLine();
                Console::output('Овнер: №' . $i++ . ' ' . $owner->fullname);
                Console::clearLine();
                Console::output('Ищем адрес: ' . $addr);
                Console::clearLine();
                Console::output('Нашли: ' . $ans);
                Console::moveCursorUp(3);
                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                Console::output($e);
            }

        }

        Console::moveCursorDown(4);
        Console::output('Всего организация с адресами: ' . Organizations::find()->andWhere('id_address is not null')->count());
        $i = 0;
        /** @var  $owner Organizations */
        foreach (Organizations::find()->andWhere('id_address is not null')->each() as $owner) {
            $transaction = $this->db->beginTransaction();
            try {
                $addr = $owner->address->name;

                $address = $this->findAddressWithHouse($addr);

                $owner->id_fias_address = $address->id;

                if(!$owner->save())
                {
                    Console::output('Ошибки' . implode(' ', $owner->getErrors()));
                }


                $ans = $address ? ($address->streetguid) : 'нет';
                Console::output('Орг: №' . $i++ . ' ' . $owner->name);
                Console::output('Ищем адрес: ' . $addr);
                Console::output('Нашли: ' . $ans);
                Console::moveCursorUp(3);

                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                Console::output($e);
            }
        }

        $timeSpent = time() - $time;
        Console::moveCursorDown(4);
        Console::output("Затрачено " . $timeSpent . " sec" . PHP_EOL);

    }

    private function findAddressWithHouse($q)
    {
        $addrs = preg_split('/\s*,\s*/', $q);
        // дом - housenum владение сооружение д. в.
        // корпус - buildnum корпус к.
        // строение - strucnum строение с.
        $pattern = '/.+[\.\s](.+)/';

        $housenum = array_filter($addrs, function ($x) {
            return preg_match('/дом|владение|сооружение|домовладение|д\.|в\./', $x);
        });
        if(!empty($housenum)){
            unset($addrs[array_keys($housenum)[0]]);
            $housenum = $housenum ? preg_filter($pattern, '\1', array_shift($housenum)) : '';
        }else{
            $housenum = null;
        }

        $buildnum = array_filter($addrs, function ($x) {
            return preg_match('/корпус|к\./', $x);
        });
        if(!empty($buildnum)){
            unset($addrs[array_keys($buildnum)[0]]);
            $buildnum = $buildnum ? preg_filter($pattern, '\1', array_shift($buildnum)) : '';
        }else{
            $buildnum = null;
        }

        $strucnum = array_filter($addrs, function ($x) {
            return preg_match('/строение|с\./', $x);
        });
        if(!empty($strucnum)){
            unset($addrs[array_keys($strucnum)[0]]);
            $strucnum = $strucnum ? preg_filter($pattern, '\1', array_shift($strucnum)) : '';
        }else{
            $housenum = null;
        }

        $addrs = implode(' ', $addrs);

        $street_res = $this->searchByFullname($addrs);
        if ($street_res){
            $street = str_replace('город Москва, ', '', $street_res['name']);
            $streetguid = $street_res['uid'];
        }
        else{
            $street = $addrs;
            $streetguid = null;
        }

        $house = null;

        if($streetguid) {
            $house_res = \app\models\db\fias\House::findOne([
                'aoguid' => $streetguid,
                'enddate' => '2079-06-06',
                'housenum' => $housenum,
                'buildnum' => $buildnum,
                'strucnum' => $strucnum
            ]);
            if($house_res){
                $house = $house_res->getHouseName();
                $houseguid = $house_res->houseguid;
            }
        }

        if(!$house){
            $house = $this->getHouseName($housenum, $buildnum, $strucnum);
            $houseguid = null;
        }


        $address = new FiasAddresses();
        $address->city = 'город Москва';
        $address->cityguid = '0c5b2444-70a0-4932-980c-b4dc0d3f02b5';
        $address->street = $street;
        $address->streetguid = $streetguid;
        $address->houseguid = $houseguid;
        $address->house = $house;
        $address->save();

        return $address;
    }


    public function searchByFullname($q = null)
    {
        /** @var Connection $db */
        $db = \Yii::$app->dbFias;

        $q = preg_replace('/[^ а-яА-ЯёЁ\d\-]+/ui', '', $q);
        $q = trim($q, ' ');
        $qarr = preg_split('/\s+/',$q);
        $tsq = array_reduce($qarr, function ($carry, $item) {
            $prefix = $carry ? ' && ' : '';
            return $carry . $prefix . "to_tsquery('russian', '$item:*')";
        });
        $tsq = 'full_vect @@ ('.$tsq . ')';

        $query = new Query;

        $result = $query->from('"addrs"')
            ->andWhere($tsq)
            ->andWhere(['parentguid' => '0c5b2444-70a0-4932-980c-b4dc0d3f02b5'])
            ->andWhere(['in', 'aolevel', [7, 91]])
            ->select('aoguid as uid, fullname as name, aolevel')
            ->one($db);

        return  $result;
    }

    public function getHouseName($housenum, $buildnum, $strucnum)
    {
        $house = [];
        if ($housenum) {
            $house[] = 'д. ' . $housenum;
        }

        if ($buildnum) {
            $house[] = 'корп. ' . $buildnum;
        }

        if ($strucnum) {
            $house[] = 'стр. ' . $strucnum;
        }

        return implode(' ', $house);
    }



/**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181210_073916_convert_addrs cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181210_073916_convert_addrs cannot be reverted.\n";

        return false;
    }
    */
}
