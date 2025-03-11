<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 07.12.18
 * Time: 16:40
 */

namespace app\modules\admin\models;


use yii\db\ActiveRecord;

/**
 * Class StatisticMosRu
 * @package app\modules\admin\models
 */
class StatisticMosRu extends ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'statistic.statistic_mos_ru';
    }
}