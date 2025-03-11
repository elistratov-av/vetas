<?php

namespace app\models\db\found_pet;

use app\models\db\ActiveRecord;

/**
 * Class AdAuthor
 * @package app\models\db\found_pet
 *
 * @property int    $id
 * @property string $sso_id         Идентификатор пользователя mos.ru
 * @property string $first_name     Имя пользователя
 * @property string $middle_name    Отчество пользователя
 * @property string $last_name      Фамилия пользователя
 * @property string $phone          Телефон пользователя
 * @property string $email          Электронная почта пользователя
 * @property string $created_at
 * @property string $updated_at
 */
class AdAuthor extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'found_pet.ad_authors';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sso_id', 'first_name', 'last_name'], 'required'],
            [['first_name', 'middle_name', 'last_name', 'phone'], 'string'],
            ['email', 'email'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * @param string $sso_id
     * @return \app\models\db\found_pet\AdAuthor|null
     */
    public static function findBySsoId($sso_id)
    {
        return static::findOne(['sso_id' => $sso_id]);
    }
}
