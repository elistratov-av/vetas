<?php

namespace app\models\db;

use yii\helpers\ArrayHelper;

/**
 * Class PetIdentificationTransferLog
 * @package app\models\db
 *
 * @property int          $id
 * @property int          $id_pet_from
 * @property int          $id_pet_to
 * @property int          $id_ident_type
 * @property array|string $identification_data
 * @property string       $created_at
 * @property string       $updated_at
 * @property int          $created_by
 * @property int          $updated_by
 */
class PetIdentificationTransferLog extends ActiveRecord
{
    /**
     * @inheritDoc
     */
    public static function tableName()
    {
        return 'public.pet_identification_transfer_log';
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['id_pet_from', 'id_pet_to', 'id_ident_type'], 'integer'],
            ['identification_data', 'safe'],
            [['created_at', 'updated_at', 'created_by', 'updated_by'], 'safe'],
        ];
    }

    /**
     * @param \app\models\db\PetIdentification $model
     */
    public function dehydrateData(PetIdentification $model)
    {
        $this->identification_data = $model->toArray();
    }

    /**
     * @param \app\models\db\PetIdentification $model
     */
    public function hydrateData(PetIdentification &$model)
    {
        $data = $this->identification_data;
        if (!empty($data) && is_array($data)) {
            ArrayHelper::remove($data, 'id');
            $model->load($data, '');
        }
    }
}
