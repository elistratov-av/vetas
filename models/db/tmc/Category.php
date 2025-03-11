<?php

namespace app\models\db\tmc;

use app\models\db\ActiveRecord;
use app\models\db\GovServices;
use Yii;

/**
 * This is the model class for table "tmc.category".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $slug
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 *
 * @property TmcBase[] $tmcs
 * @property GovServices[] $govServices
 *
 */
class Category extends ActiveRecord
{
    /**
     * Slugs
     */
    const SLUG_VETERINARY_SERTIFICATE_FORMS = 'veterinary_certificate_forms'; //Бланки ветеринарного свидетельства

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tmc.category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['slug'], 'string', 'max' => 50],
            [['created_by', 'updated_by'], 'default', 'value' => null],
            [['created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'description'], 'string', 'max' => 255],
            [['name'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Наименование категории',
            'description' => 'Описание',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTmcs()
    {
        return $this->hasMany(TmcBase::class, ['id' => 'id_tmc', 'type' => 'type_tmc'])
            ->viaTable('tmc.category_to_tmc', ['id_category' => 'id']);

    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGovServices()
    {
        return $this->hasMany(GovServices::class, ['id' => 'id_tmc', 'type' => 'type_tmc'])
            ->viaTable('tmc.category_to_gov_services', ['id_service' => 'id']);
    }
}
