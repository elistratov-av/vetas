<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 03.12.18
 * Time: 13:58
 */

namespace app\modules\admin\models;


use yii\db\ActiveRecord;


/**
 * Class Addresses
 * @package app\modules\admin\models
 */
class Addresses extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'addresses';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organization::class, ['id' => 'id_address']);
    }
}