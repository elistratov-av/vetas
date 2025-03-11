<?php

namespace app\modules\soap\models\etp;

use app\models\MosruNotification;

/**
 * Класс для обработки статусного сообщения 1010 от ЕТП для  неавторизованных пользователей - "Запись на прием"
 *
 * Class CoordinateMessageUnauthorized
 * @package app\modules\soap\models\etp
 *
 * @property null|string $mobile_phone
 */
class CoordinateMessageUnauthorized extends CoordinateMessage
{
    /** @var null|string */
    public $mobile_phone;

    /**
     * @throws \Exception
     */
    public function initAttributes() : void
    {
        parent::initAttributes();

        if (!is_null($this->birthdate_animal)) {
            $date = new \DateTime($this->birthdate_animal);
            $this->birthdate_animal = $date->format('Y-m-d');
        }
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules['required'] = [
            [
                'service_number', 'department', 'responsible', // общие данные
                'last_name', 'first_name', // владелец
                'species_id', // животное
                'user_id', 'service_id', 'visit_date', 'organization_id', 'call_to_home' // прием
            ],
            'required'
        ];
        $rules[] = ['mobile_phone', 'safe'];

        return $rules;
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function process(): void
    {
        $this->initAttributes();
        if ($this->validate()) {
            \Yii::$app->db->transaction(function () {
                $pet = $this->getPet();
                $petOwner = $this->createPetOwner();
                $this->linkPetAndOwner($pet, $petOwner);
                $attributes = [];
                if (!empty($this->mobile_phone)) {
                    // для 'Инкогнито' мы не можем сохранять мобильный телефон в контакты из-за ограничения уникальности
                    // (велика вероятность, что пользователь 'Инкогнито' часто будет использовать при записи один и тот же номер,
                    // 'склейки' для 'Инкогнито' на данный момент не предусмотрено)
                    $attributes['description'] = 'Мобильный телефон: ' . $this->mobile_phone;
                }
                $visit = $this->createVisit($pet, $petOwner, $attributes);
                $this->saveMessage($visit);

                MosruNotification::visitCreate($visit->id);
            });
        } else {
            $this->sendErrorMessage(implode("\r\n", $this->getErrorSummary(true)));
        }
    }
}
