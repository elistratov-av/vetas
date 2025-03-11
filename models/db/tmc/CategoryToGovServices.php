<?php

namespace app\models\db\tmc;

use app\models\db\ActiveRecord;
use app\models\db\GovServices;

/**
 * This is the model class for table "tmc.category_to_gov_services".
 *
 * @property int $id
 * @property int $id_category
 * @property int $id_service
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Category[] $categories
 */
class CategoryToGovServices extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tmc.category_to_gov_services';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_category', 'id_service'], 'required'],
            [['id_category', 'id_service', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['id_category', 'id_service', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['id_category', 'id_service'], 'unique', 'targetAttribute' => ['id_category', 'id_service']],
            [['id_service'], 'exist', 'skipOnError' => true, 'targetClass' => GovServices::class, 'targetAttribute' => ['id_service' => 'id']],
            [['id_category'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['id_category' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_category' => 'Id Category',
            'id_service' => 'Id Service',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
