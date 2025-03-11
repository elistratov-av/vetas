<?php

namespace app\models\db;

/**
 * Class PetsToOwnerTransferLog
 * @package app\models\db
 *
 * @property int          $id
 * @property int          $id_pet_from
 * @property int          $id_pet_to
 * @property array|string $links_pet_from
 * @property array|string $links_pet_to
 * @property string       $created_at
 * @property string       $updated_at
 * @property int          $created_by
 * @property int          $updated_by
 */
class PetsToOwnerTransferLog extends ActiveRecord
{
    /**
     * @inheritDoc
     */
    public static function tableName()
    {
        return 'public.pets_to_owner_transfer_log';
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['id_pet_from', 'id_pet_to'], 'integer'],
            [['links_pet_from', 'links_pet_to'], 'safe'],
            [['created_at', 'updated_at', 'created_by', 'updated_by'], 'safe'],
        ];
    }
}
