<?php

namespace app\models\db\odopm;

use app\models\db\ActiveRecord;

/**
 * Справочник катологов ОДОПМ
 *
 * @property int    $id
 * @property int    $parent_id
 * @property int    $id_odopm
 * @property string $name
 * @property string $period
 * @property string $created_at
 * @property string $updated_at
 *
 * Class OdopmCatalogs
 * @package app\models\db\odopm
 *
 * @property OdopmCatalogsItem[] $items
 * @property OdopmAttributesSpecification[] $specifications
 */
class OdopmCatalogs extends ActiveRecord
{
    /**
     * @inheritDoc
     */
    public static function tableName()
    {
        return 'odopm.odopm_catalogs';
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['id', 'parent_id', 'id_odopm'], 'integer'],
            [['name', 'period'], 'string', 'max' => 255],
            [['created_at', 'updated_at'], 'safe']
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(OdopmCatalogsItem::class, ['id_catalog' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSpecifications()
    {
        return $this->hasMany(OdopmAttributesSpecification::class, ['id_catalog' => 'id']);
    }
}
