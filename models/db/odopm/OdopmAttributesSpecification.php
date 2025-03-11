<?php

namespace app\models\db\odopm;

use Yii;

/**
 * This is the model class for table "odopm.odopm_attributes_specification".
 *
 * @property int $id
 * @property int $id_catalog
 * @property int $attribute_id
 * @property int $type_id
 * @property string $name
 * @property string $type
 * @property bool $is_primary
 * @property bool $is_edit
 * @property bool $is_required
 * @property string $field_mask
 * @property string $tech_name
 * @property int $max_length
 * @property string $max_length_decimal
 * @property int $dictionary_id
 * @property int $ref_catalog_id
 * @property bool $is_deleted
 * @property bool $is_tmp_deleted
 * @property bool $is_multi
 *
 * @property OdopmCatalogs $catalog
 */
class OdopmAttributesSpecification extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'odopm.odopm_attributes_specification';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_catalog', 'attribute_id', 'type_id', 'max_length', 'dictionary_id', 'ref_catalog_id'], 'default', 'value' => null],
            [['id_catalog', 'attribute_id', 'type_id', 'max_length', 'dictionary_id', 'ref_catalog_id'], 'integer'],
            [['is_primary', 'is_edit', 'is_required', 'is_deleted', 'is_tmp_deleted', 'is_multi'], 'boolean'],
            [['type', 'tech_name'], 'string', 'max' => 32],
            [['name', 'field_mask', 'max_length_decimal'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_catalog' => 'Id Catalog',
            'attribute_id' => 'Attribute ID',
            'type_id' => 'Attribute Type ID',
            'name' => 'Attribute Name',
            'type' => 'Attribute Type',
            'is_primary' => 'Attribute Is Primary',
            'is_edit' => 'Attribute Is Edit',
            'is_required' => 'Attribute Is Required',
            'field_mask' => 'Attribute Field Mask',
            'tech_name' => 'Attribute Tech Name',
            'max_length' => 'Attribute Max Length',
            'max_length_decimal' => 'Attribute Max Length Deci',
            'dictionary_id' => 'Attribute Dictionary ID',
            'ref_catalog_id' => 'Attribute Ref Catalog ID',
            'is_deleted' => 'Attribute Is Deleted',
            'is_tmp_deleted' => 'Attribute Is Tmp Deleted',
            'is_multi' => 'Attribute Is Multi',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCatalog()
    {
        return $this->hasOne(OdopmCatalogs::class, ['id_odopm' => 'id_catalog']);
    }

}
