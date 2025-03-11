<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 30.10.19
 * Time: 12:21
 */

namespace app\models\db\animalid;

use app\models\db\ActiveRecord;

/**
 * This is the model class for table "public.organizations".
 *
 * @property integer $id
 * @property string $fullname
 * @property string $phone
 * @property string $email
 * @property string $address
 */
class Companies extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'animalid.companies';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fullname', 'email'], 'required'],
            [['fullname', 'phone', 'email', 'address'], 'safe'],
        ];
    }

}