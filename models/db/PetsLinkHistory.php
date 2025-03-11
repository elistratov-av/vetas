<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 03.02.2021
 * Time: 12:58
 */

namespace app\models\db;

/**
 * Class PetOwnersLinkHistory
 * @package app\models\db
 *
 * @property        int         $id
 * @property        int         $id_pet_main
 * @property        int         $id_pet_duplicate
 * @property        string|array      $values                 скопированные значения полей
 * @property        string      $created_at
 * @property        string      $updated_at
 * @property        boolean     $enabled
 */
class PetsLinkHistory extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'public.pets_link_history';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPetMain() {
        return $this->hasOne(Pets::class, ['id' => 'id_pet_main']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPetDuplicate() {
        return $this->hasOne(Pets::class, ['id' => 'id_pet_duplicate']);
    }

}