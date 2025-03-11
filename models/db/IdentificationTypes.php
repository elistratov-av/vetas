<?php

namespace app\models\db;

use Yii;
use yii\base\InvalidValueException;

/**
 * This is the model class for table "public.identification_types".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $created_at
 * @property string $updated_at
 */
class IdentificationTypes extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.identification_types';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 50],
            [['description'], 'string', 'max' => 255],
            [['name'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Находит и возвращает id записи "чип" в таблице identification_types
     * Генерирует InvalidValueException если не нашел
     * Можно использовать кеш (по-умолчанию отключен) для запроса в БД (см Query::cache)
     * Тк этот тип имеет особые проверки и бизнес-логику, его поиск вынесено в этот метод
     *
     *
     * @throws \InvalidValueException
     *
     * @param int $cache_query Кешировать запрос к БД (по-умолчанию отключено)
     * @return false|string|null
     */
    public static function findIdentificationTypeId($identType, $cache_query = -1)
    {
        $identification_types_chip_id = self::find()
            ->select('id')
            ->where(['name' => $identType])
            ->cache($cache_query)
            ->scalar();

        if (empty($identification_types_chip_id)) {
            throw new InvalidValueException('Не удалось найти id записи "чип" в таблице identification_types');
        }

        return $identification_types_chip_id;
    }

}