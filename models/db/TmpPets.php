<?php

namespace app\models\db;

use Yii;

/**
 * Данные питомцев неавторизованных пользователей (заявки mos.ru)
 *
 * @property int $id
 * @property string|null $birthday
 * @property string|null $name
 * @property string|null $sex
 * @property int|null $id_species
 * @property int|null $id_breed
 * @property string $created_at
 * @property string|null $updated_at
 */
class TmpPets extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pets_tmp';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [[
                // Модель создаётся только при создании модели Pets, валидирующей эти же данные
                'birthday',
                'name',
                'sex',
                'id_species',
                'id_breed',
                'created_at',
                'updated_at',
            ], 'safe'],
        ];
    }
}
