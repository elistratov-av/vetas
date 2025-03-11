<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 21.01.19
 * Time: 13:19
 */

namespace app\modules\admin\models;

use app\models\db\ActiveRecord;
use app\models\db\IdentificationTypes;

class PetsOldIdentification extends ActiveRecord
{
    public static function tableName()
    {
        return 'temp.pets_old_identification';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPet()
    {
        return $this->hasOne(Pets::class, ['id' => 'id_pet']);
    }

    public function getIdentType()
    {
        return $this->hasOne(IdentificationTypes::class, ['id' => 'id_ident_type']);
    }

}