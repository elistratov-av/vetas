<?php

namespace app\common\behaviors;

use app\common\components\entity\EntityResourceFactory;
use app\models\db\FiasAddresses;
use yii\helpers\ArrayHelper;

trait FiasTrait
{
    /**
     * @return FiasAddresses|null
     */
    protected function getFiasAddresses($postData)
    {
        $address = new FiasAddresses();

        $address->regionguid = ArrayHelper::getValue($postData,'regionguid');
        $address->cityguid = ArrayHelper::getValue($postData,'cityguid');
        $address->streetguid = ArrayHelper::getValue($postData,'streetguid');
        $address->houseguid = ArrayHelper::getValue($postData,'houseguid');
        $address->roomguid = ArrayHelper::getValue($postData,'roomguid');

        $address->region = ArrayHelper::getValue($postData,'region');
        $address->city = ArrayHelper::getValue($postData,'city');
        $address->street = ArrayHelper::getValue($postData,'street');
        $address->house = ArrayHelper::getValue($postData,'house');
        $address->room = ArrayHelper::getValue($postData,'room');

        $address->lat = ArrayHelper::getValue($postData, 'latitude', ArrayHelper::getValue($postData, 'lat'));
        $address->lon = ArrayHelper::getValue($postData, 'longitude', ArrayHelper::getValue($postData, 'lon'));

        $address->oktmo = (string)ArrayHelper::getValue($postData, 'oktmo');

        if($address->validate() && $address->save())
            return $address;
        else
            return null;
    }

    /**
     * @return array|null|static
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function getAddress()
    {
        /** @var FiasAddresses $fiasAddress */
        if (!empty($this->owner->id_fias_address) &&
            $fiasAddress = FiasAddresses::findOne(['id' => $this->owner->id_fias_address])
        ) {
            return $fiasAddress;
        } else {
            $resource = EntityResourceFactory::getResource('addresses');
            return $resource::findOne($this->owner->id_address);
        }
    }
}
