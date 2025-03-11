<?php

namespace app\modules\mdm\models;

use yii\base\Model;

class Owner extends Model
{
    /**
     * Идентификатор пользователя sso_id
     * @var string
     */
    public $sso_id;

    /**
     * Фамилия пользователя
     * @var string
     */
    public $last_name;

    /**
     * Имя пользователя
     * @var string
     */
    public $first_name;

    /**
     * Отчество Пользователя
     * @var string
     */
    public $middle_name;

    /**
     * СНИЛС
     * @var string
     */
    public $snils;

    /**
     * Адрес электронной почты
     * @var string
     */
    public $email;

    /**
     * Номер мобильного телефона
     * @var string
     */
    public $phone;

    /**
     * Признак удаления профиля
     * @var bool
     */
    public $deleted = false;

    public function rules()
    {
        return [
            [
                'snils',
                function($attribute, $params) {
                    if (empty($this->snils) && empty($this->phone)) {
                        $this->addError($attribute, 'Необходимо указать один из следующих параметров: snils, phone');
                    }
                },
                'skipOnEmpty' => false
            ],
            [['sso_id', 'last_name', 'first_name'], 'required', 'message' => 'Не передан параметр {attribute}'],
            ['email', 'email', 'enableIDN' => true]
        ];
    }
}
