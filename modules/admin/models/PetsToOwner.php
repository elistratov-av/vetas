<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 21.01.19
 * Time: 12:38
 */

namespace app\modules\admin\models;

use yii\db\ActiveRecord;

class PetsToOwner extends ActiveRecord
{
    public static function tableName()
    {
        return 'pets_to_owner';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPets()
    {
        return $this->hasMany(Pets::class(), ['id' => 'id_pet']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOwner()
    {
        return $this->hasOne(Owners::class(), ['id' => 'id_owner']);
    }

}