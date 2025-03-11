<?php

namespace app\models\db\admin;

use app\models\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Class SecuritySettings
 * @package app\models\db\admin
 *
 * @property int $id
 * @property int $jwt_token_ttl
 * @property int $allowed_login_attempts
 * @property int $password_duration
 * @property int $password_min_duration
 * @property int $password_compare_previous
 * @property int $password_min_length
 * @property bool $password_contains_letters
 * @property bool $password_both_case
 * @property bool $password_contains_digits
 * @property bool $password_contains_symbols
 * @property int $password_min_changed_symbols
 * @property bool $external_services_auth_enabled
 * @property string $created_at
 * @property string $updated_at
 * @property string $created_by
 * @property string $updated_by
 */
class SecuritySettings extends ActiveRecord
{
    const DEFAULT_ID = 1;

    /**
     * @inheritDoc
     */
    public static function tableName()
    {
        return 'admin.security_settings';
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [
                'jwt_token_ttl',
                'integer',
                'min' => 1,
                'max' => 31536000,
                'tooSmall' => 'Продолжительность срока действия токена не может быть меньше 1 секунды',
                'tooBig' => 'Продолжительность срока действия токена не может быть больше 1 года',
            ],
            ['allowed_login_attempts', 'integer', 'min' => 1, 'max' => 10],
            ['password_duration', 'integer', 'min' => 1, 'max' => 365],
            ['password_min_duration', 'integer', 'min' => 1, 'max' => 720],
            ['password_compare_previous', 'integer', 'min' => 1, 'max' => 10],
            ['password_min_length', 'integer', 'min' => 6, 'max' => 100],
            [[
                'password_contains_letters',
                'password_both_case',
                'password_contains_digits',
                'password_contains_symbols',
                'external_services_auth_enabled',
            ], 'boolean'],
            ['password_contains_symbols', 'validatePasswordStrength'],
            ['password_min_changed_symbols', 'integer', 'min' => 1, 'max' => 10],
            [['created_at', 'updated_at', 'created_by', 'updated_by'], 'safe'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
            'jwt_token_ttl' => 'Продолжительность срока действия токена (в секундах)',
            'allowed_login_attempts' => 'Количество неудачных попыток логина',
            'password_duration' => 'Максимальное время действия пароля (в днях)',
            'password_min_duration' => 'Минимальное время действия пароля (в часах)',
            'password_compare_previous' => 'Запрет на использование последних N паролей',
            'password_min_length' => 'Минимальная длина пароля (символов)',
            'password_contains_letters' => 'Содержит латинские буквы',
            'password_both_case' => 'И в верхнем и в нижнем регистре',
            'password_contains_digits' => 'Содержит цифры',
            'password_contains_symbols' => 'Содержит спецсимволы !@$%^&-+?',
            'password_min_changed_symbols' => 'Минимальное количество измененных символов',
            'external_services_auth_enabled' => 'Включить авторизацию внешних систем',
        ];
    }

    /**
     * Merge params with app params
     */
    public static function loadParams()
    {
        $model = static::find()
            ->where(['id' => self::DEFAULT_ID])
            ->limit(1)
            ->one();

        if ($model !== null) {
            $settings = $model->toArray();
            foreach (['id', 'created_at', 'updated_at', 'created_by', 'updated_by'] as $key) {
                unset($settings[$key]);
            }
            $params = ArrayHelper::merge(\Yii::$app->params, $settings);
            \Yii::$app->params = $params;
        }
    }

    /**
     * @param string                          $attribute is the name of the attribute to be validated
     * @param array                           $params    contains the value of [[params]] that you specify when declaring the inline validation rule
     * @param \yii\validators\InlineValidator $validator is a reference to related [[InlineValidator]] object (since 2.0.11)
     * @see \yii\validators\InlineValidator
     */
    public function validatePasswordStrength($attribute, $params, $validator)
    {
        if ($this->hasErrors()) {
            return;
        }

        foreach (['password_contains_letters', 'password_contains_digits', 'password_contains_symbols'] as $attr) {
            if ($this->$attr) {
                return;
            }
        }

        $this->addError('password_contains_letters', 'Необходимо выбрать хотя бы одну настройку');
    }
}
