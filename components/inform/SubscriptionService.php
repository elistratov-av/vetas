<?php

namespace app\common\components\inform;

use app\models\db\Contacts;
use app\models\db\PetOwners;
use yii\base\Component;
use yii\helpers\Json;
use yii\helpers\StringHelper;

class SubscriptionService extends Component
{
    /**
     * @param PetOwners  $owner
     * @param array|null $types
     * @return Contacts[]|array|\yii\db\ActiveRecord[]
     */
    public static function getOwnerSubscriptions(PetOwners $owner, array $types = null)
    {
        $query = Contacts::find()
            ->where(['contacts.entity_type' => 'pet_owner'])
            ->andWhere(['contacts.entity_id' => $owner->id]);

        if ($types) {
            $query->leftJoin('contact_types ct', 'ct.id = contacts.id_contact_type')
                ->andWhere(['ct.type' => $types]);
        }

        return $query->all();
    }

    /**
     * @param int    $id_contact
     * @param string $value (email или телефон)
     * @param string|null $sso_id
     * @return string
     */
    public static function encodeUnsubscribeToken($id_contact, $value, $sso_id)
    {
        $data = [
            'id_contact' => $id_contact,
            'value' => $value,
            'sso_id' => $sso_id
        ];

        $token = \Yii::$app->security->encryptByKey(Json::encode($data), \Yii::$app->params['unsubscribeValidationKey']);

        return StringHelper::base64UrlEncode($token);
    }

    /**
     * @param string $token
     * @return array
     */
    public static function decodeUnsubscribeToken($token)
    {
        $token = StringHelper::base64UrlDecode($token);
        $data = \Yii::$app->security->decryptByKey($token, \Yii::$app->params['unsubscribeValidationKey']);

        return Json::decode($data);
    }
}
