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
 * @property        int         $id_owner_main
 * @property        int         $id_owner_duplicate
 * @property        string|array      $values                 скопированные значения полей
 * @property        string      $contacts               скопированные значения контактов
 * @property        string      $created_at
 * @property        string      $updated_at
 * @property        boolean     $enabled
 */
class PetOwnersLinkHistory extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'public.pet_owners_link_history';
    }

    /**
     * @return PetOwners
     */
    public function getOwnerMain() {
        return $this->hasOne(PetOwners::class, ['id' => 'id_owner_main']);
    }

    /**
     * @return PetOwners
     */
    public function getOwnerDuplicate() {
        return $this->hasOne(PetOwners::class, ['id' => 'id_owner_duplicate']);
    }

}