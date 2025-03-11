<?php

namespace app\models\db;

use yii\helpers\ArrayHelper;

/**
 * Class ContactTypes
 * @package app\models\db
 *
 * @property int    $id
 * @property string $name
 * @property string $type
 * @property string $entity_type
 * @property int    $created_by
 * @property int    $updated_by
 * @property string $created_at
 * @property string $updated_at
 */
class ContactTypes extends ActiveRecord
{
    const ENTITY_TYPE_PET_OWNER = 'pet_owner';
    const ENTITY_TYPE_ORGANIZATION = 'organization';

    const TYPE_PHONE = 'phone';
    const TYPE_EMAIL = 'email';


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.contact_types';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'type', 'entity_type'], 'required'],
            [['name', 'type', 'entity_type'], 'filter', 'filter' => 'trim'],
            [['name', 'type', 'entity_type'], 'filter' => 'strip_tags'],
            [['name', 'entity_type'], 'string', 'max' => 255],
            ['type', 'string', 'max' => 50],
            ['name', 'unique'],
        ];
    }

    /**
     * @param string $entity_type
     * @param string $type
     * @return array
     */
    public static function typeOptions($entity_type = null, $type = null)
    {
        $query = static::find()
            ->orderBy([
                'entity_type' => SORT_ASC,
                'type' => SORT_ASC,
                'id' => SORT_ASC,
            ])
            ->asArray();
        if (!empty($entity_type)) {
            $query->andWhere(['entity_type' => $entity_type]);
        }
        if (!empty($type)) {
            $query->andWhere(['type' => $type]);
        }
        $models = $query->all();

        return ArrayHelper::map($models, 'id', 'name');
    }

    /**
     * Находит и возвращает id записи по имени
     *
     * @throws \InvalidValueException
     *
     * @param int $cache_query Кешировать запрос к БД (по-умолчанию отключено)
     * @return false|string|null
     */
    public static function findContactTypeId($contactType, $entity_type, $cache_query = -1)
    {
        $contact_type_id = self::find()
            ->select('id')
            ->where(['AND', ['name' => $contactType], ['entity_type' => $entity_type]])
            ->cache($cache_query)
            ->scalar();

        if (empty($contact_type_id)) {
            throw new InvalidValueException('Не удалось найти id записи contact_types');
        }

        return $contact_type_id;
    }
}
