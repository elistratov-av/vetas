<?php

namespace app\modules\soap\v2\models\etp\members;

use app\common\helpers\PhoneHelper;
use yii\base\Model;

/**
 * Class Declarant
 * @package app\modules\soap\v2\models\etp\members
 */
class Declarant extends Model
{
    /**
     * @var string
     */
    public $LastName;
    /**
     * @var string
     */
    public $FirstName;
    /**
     * @var string
     */
    public $MiddleName;
    /**
     * @var string
     */
    public $BirthDate;
    /**
     * @var string
     */
    public $Snils;
    /**
     * @var string
     */
    public $MobilePhone;
    /**
     * @var string
     */
    public $EMail;
    /**
     * @var string
     */
    public $SsoId;
    /**
     * @var \app\modules\soap\v2\models\etp\members\FactAddress
     */
    public $FactAddress;

    /**
     * @var bool
     */
    private $authorized = true;
    /**
     * @var bool
     */
    private $monitoring = false;

    /**
     * @inheritDoc
     */
    public function init()
    {
        if (is_array($this->FactAddress) && !empty($this->FactAddress)) {
            $this->FactAddress = new FactAddress($this->FactAddress);
        }
        if (!empty($this->MobilePhone)) {
            $this->MobilePhone = PhoneHelper::extractMosRuPhoneNumber($this->MobilePhone);
        }
        if (!is_null($this->BirthDate)) {
            $date = new \DateTime($this->BirthDate);
            $this->BirthDate = $date->format('Y-m-d');
        }

        $params = \Yii::$app->getModule('soap')->params;

        $this->monitoring = (isset($this->SsoId) && $this->SsoId === $params['monitoring']['SsoId']);

        if (isset($this->LastName)
            && isset($this->FirstName)
            && $this->LastName === $params['unauthorized_message']['LastName']
            && $this->FirstName === $params['unauthorized_message']['FirstName']
        ) {
            $this->authorized = false;
        }
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['LastName', 'FirstName'], 'required'],
            [['LastName', 'FirstName', 'MiddleName', 'Snils', 'SsoId'], 'string'],
            ['EMail', 'email'],
            ['BirthDate', 'date', 'format' => 'php:Y-m-d'],
            ['MobilePhone', 'required', 'when' => function ($model) {
                /* @var $model $this */
                return $model->isAuthorized() === true;
            }],
        ];
    }

    /**
     * @return bool
     */
    public function isAuthorized()
    {
        return $this->authorized;
    }

    /**
     * @return bool
     */
    public function isMonitoring()
    {
        return $this->monitoring;
    }
}
