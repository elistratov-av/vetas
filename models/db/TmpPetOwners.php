<?php

namespace app\models\db;

use Yii;

/**
 * Данные неавторизованных владельцев животных (заявки mos.ru)
 *
 * @property int $id
 * @property string $f_fio Фамилия
 * @property string $i_fio Имя
 * @property string|null $o_fio Отчество
 * @property string $fullname Полное имя
 * @property string|null $birthday Дата рождения
 * @property string|null $snils СНИЛС
 * @property string $created_at
 * @property string|null $updated_at
 *
 * @property TmpPets[] $petTmps
 */
class TmpPetOwners extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pet_owners_tmp';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [[
                // Модель создаётся только при создании модели PetOwners, валидирующей эти же данные
                'f_fio',
                'i_fio',
                'o_fio',
                'fullname',
                'birthday',
                'snils',
                'created_at',
                'updated_at',
            ], 'safe'],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTmpPets()
    {
        return $this->hasMany(Pets::class, ['id' => 'id_pet_tmp'])->viaTable('pets_to_owner_tmp', ['id_owner_tmp' => 'id']);
    }
}
