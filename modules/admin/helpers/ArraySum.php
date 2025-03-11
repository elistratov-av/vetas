<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 01.03.19
 * Time: 10:46
 */

namespace app\modules\admin\helpers;


class ArraySum
{
    public static function getSum($array)
    {
        $sumArray=[];
        foreach ($array as $k=>$subArray) {
            foreach ($subArray as $id=>$value) {
                $value = floatval($value);
                if(isset($sumArray[$id])) {
                    $sumArray[$id]+=$value;
                } else {
                    $sumArray[$id]=$value;
                }
            }
        }
        return $sumArray;
    }

}