<?php

namespace app\models\db\odopm;

use app\models\db\ActiveRecord;
use app\models\db\Contacts;
use app\models\db\Organizations;

/**
 * Связь сущностей ВЕТАС с каталогами ОДОПМ
 *
 * Class OdopmCatalogsItem
 * @property integer $id
 * @property integer $object_id
 * @property integer $id_catalog
 * @property integer $entity_id
 * @property integer $global_id
 * @property string $entity_type
 * @package app\models\db\odopm
 */
class OdopmCatalogsItem extends ActiveRecord
{
    const TYPE_ORGANIZATION = 'organization';
    const TYPE_SCHEDULE = 'schedule';
    const TYPE_CONTACT = 'contact';

    const ID_ATTRIBUTE_GLOBAL_ID = -1;
    const ID_ATTRIBUTE_SYSTEM_OBJ_ID = -2;
    const ID_ATTRIBUTE_SIGN = -4;

    public static function tableName()
    {
        return 'odopm.odopm_catalogs_item';
    }

    public function rules()
    {
        return [
            [['id', 'object_id', 'id_catalog', 'entity_id', 'global_id'], 'integer'],
            [['entity_type'], 'string', 'max' => 50],
            [['global_id'], 'unique']
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organizations::class, ['entity_id' => 'id'])
            ->where(['odopm_catalogs_item.entity_type' => self::TYPE_ORGANIZATION]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContact()
    {
        return $this->hasOne(Contacts::class, ['entity_id' => 'entity_id'])
            ->where(['odopm_catalogs_item.entity_type' => self::TYPE_CONTACT]);
    }


}
