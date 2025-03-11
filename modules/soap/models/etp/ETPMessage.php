<?php

namespace app\modules\soap\models\etp;

use app\models\db\GovServices;
use app\modules\soap\models\PetOwners;
use app\modules\soap\models\Pets;
use app\modules\soap\models\Visits;

/**
 * Class ETPMessage
 * @package app\modules\soap\models\etp
 *
 * @property CoordinateMessage $coordinateMessage
 * @property Visits $visits
 */
class ETPMessage extends \app\models\db\etp\ETPMessage
{
    const
        SCENARIO_CREATE = 'create'
    ;

    /** @var CoordinateMessage */
    public $coordinateMessage;


    public function rules()
    {
        return [
            ['coordinateMessage', 'required', 'on' => [ETPMessage::SCENARIO_CREATE]],
            [['service_number', 'visit_id', 'message'], 'required']
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVisits()
    {
        return $this->hasOne(Visits::class, ['id' => 'visit_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAnimal()
    {
        return $this
            ->hasOne(Pets::class, ['id' => 'id_pet'])
            ->viaTable('visits', ['id' => 'visit_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOwner()
    {
        return $this
            ->hasOne(PetOwners::class, ['id' => 'id_owner'])
            ->viaTable('visits', ['id' => 'visit_id']);
    }

    /**
     * @return CoordinateMessage
     */
    public function getCoordinateMessageInstance()
    {
        if (!($this->coordinateMessage instanceof CoordinateMessage)) {
            $this->coordinateMessage = new CoordinateMessage($this->message['xml']['CoordinateDataMessage']);
            $this->coordinateMessage->initAttributes();
        }

        return $this->coordinateMessage;
    }
}
