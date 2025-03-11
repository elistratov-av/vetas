<?php

use app\commands\migrate\Migration;
use app\models\db\FiasAddresses;
use yii\helpers\Console;

/**
 * Class m181211_093639_fix_houses_in_fias_addresses
 */
class m181211_093639_fix_houses_in_fias_addresses extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function Up()
    {
        $sql = <<<SQL
with ids as ( select id_address as adid, id_fias_address as faid
              from organizations
                union
              select id_address as adis, id_fias_address as faid
              from pet_owners)
select fa.id as faid, ad.id as adid, fa.full_address, ad.name
from fias_addresses fa, addresses ad, ids
where fa.id = ids.faid and ad.id = ids.adid
SQL;

        $query = new \yii\db\Query();
        $query->from(new \yii\db\Expression("($sql) as addrs"));

        foreach ($query->each() as $row) {
            $transaction = $this->db->beginTransaction();
            try {
                $addrs = $row['name'];
                if(strstr($addrs, ', д. ')) continue;
                $addrs = preg_split('/\s*,\s*/', $addrs);
                if(!$addrs) continue;

                $fias = FiasAddresses::findOne($row['faid']);
                if(!$fias) continue;

                $pattern = '/.+[\.\s](.+)/';

                $housenum = array_filter($addrs, function ($x) {
                    return preg_match('/дом|владение|сооружение|домовладение|д\.|в\./', $x);
                });
                if(!empty($housenum)){
                    $housenum = $housenum ? preg_filter($pattern, '\1', array_shift($housenum)) : '';
                }else{
                    $housenum = '';
                }

                $buildnum = array_filter($addrs, function ($x) {
                    return preg_match('/корпус|к\./', $x);
                });
                if(!empty($buildnum)){
                    $buildnum = $buildnum ? preg_filter($pattern, '\1', array_shift($buildnum)) : '';
                }else{
                    $buildnum = '';
                }

                $strucnum = array_filter($addrs, function ($x) {
                    return preg_match('/строение|с\./', $x);
                });
                if(!empty($strucnum)){
                    $strucnum = $strucnum ? preg_filter($pattern, '\1', array_shift($strucnum)) : '';
                }else{
                    $strucnum = '';
                }

                $house = null;
                $house_res = null;

                if($fias->streetguid) {
                    $house_res = \app\models\db\fias\House::find()
                        ->andWhere(['aoguid' => $fias->streetguid,])
                        ->andWhere(['housenum' => $housenum])
                        ->andWhere(['buildnum' => $buildnum])
                        ->andWhere(['strucnum' => $strucnum])
                        ->andWhere(['enddate' => '2079-06-06 00:00:00'])
                        ->one();

                    if($house_res){
                        $house = $house_res->getHouseName();
                        $houseguid = $house_res->houseguid;
                    }
                }

                if(!$house){
                    $house = $this->getHouseName($housenum, $buildnum, $strucnum);
                    $houseguid = null;
                }

                $fias->house = $house;
                $fias->houseguid = $houseguid;
                $fias->save();
                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                Console::output($e);
            }
        }


    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181211_093639_fix_houses_in_fias_addresses cannot be reverted.\n";

        return false;
    }

    public function getHouseName($housenum, $buildnum, $strucnum)
    {
        $house = [];
        if ($housenum) {$house[] = 'д. ' . $housenum;}
        if ($buildnum) {$house[] = 'корп. ' . $buildnum;}
        if ($strucnum) {$house[] = 'стр. ' . $strucnum;}
        return implode(' ', $house);
    }


    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181211_093639_fix_houses_in_fias_addresses cannot be reverted.\n";

        return false;
    }
    */
}
