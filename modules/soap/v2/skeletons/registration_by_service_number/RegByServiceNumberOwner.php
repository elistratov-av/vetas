<?php

namespace app\modules\soap\v2\skeletons\registration_by_service_number;

use yii\base\Model;
use yii\base\UnknownPropertyException;

/**
 * Class RegByServiceNumberOwner
 * @package app\modules\soap\v2\skeletons\registration_by_service_number
 */
class RegByServiceNumberOwner extends Model
{
    /**
     * @soap
     * @var string {minOccurs=1, maxOccurs=1}
     */
    public $FirstName;

    /**
     * @var string {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $LastName;

    /**
     * @var string {nilable=true, minOccurs=0, maxOccurs=1}
     * @soap
     */
    public $MiddleName;

    /**
     * @var string {minOccurs=1, maxOccurs=1}
     * @soap
     */
    public $MobilePhone;

    /**
     * @inheritDoc
     */
    public function __set($name, $value)
    {
        try {
            parent::__set($name, $value);
        } catch (UnknownPropertyException $e) {
            // просто игнорируем
        }
    }
}
