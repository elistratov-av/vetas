<?php

namespace app\models\db;

use app\common\validators\FullTrimValidator;

/**
 * Class Documents
 * @package app\models\db
 *
 * @property int $id
 * @property int $file_id
 * @property int $type_id
 * @property string $status
 * @property string $number
 * @property string $date
 * @property string $name
 * @property int $created_by
 * @property string $created_date
 * @property string $protected_at
 *
 * @property \app\models\db\Files $file
 * @property \app\models\db\DocumentTypes $type
 * @property \app\models\db\Users $user
 */
class Documents extends ActiveRecord
{
    const PROJECT = 'PROJECT';
    const RESULT = 'RESULT';

    const DOC_STATUS = [
        self::PROJECT => 'Проект',
        self::RESULT => 'Итог',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'documents';
    }

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            // [['file_id', 'type_id', 'number', 'date', 'name', 'created_by', 'created_date'], 'required'],
            [['file_id', 'type_id', 'created_by'], 'integer'],
            [['date'], 'date', 'format' => 'php:Y-m-d'],
            [['created_date', 'protected_at',], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
            ['file_id', 'exist', 'skipOnError' => true, 'targetClass' => Files::class, 'targetAttribute' => ['file_id' => 'id']],
            ['type_id', 'exist', 'skipOnError' => true, 'targetClass' => DocumentTypes::class, 'targetAttribute' => ['type_id' => 'id']],
            ['created_by', 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['created_by' => 'id']],
            /*
            [['status', 'number', 'name'], 'string'],
            [['status', 'number', 'name'], 'filter', 'filter' => 'trim'],
            [['status', 'number', 'name'], 'filter', 'filter' => 'strip_tags'],
            [['status', 'number', 'name'], FullTrimValidator::class],
            ['status', 'in', 'range' => array_keys(self::DOC_STATUS)],
            */
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFile()
    {
        return $this->hasOne(Files::class, ['id' => 'file_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocumentType()
    {
        return $this->hasOne(DocumentTypes::class, ['id' => 'type_id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::class, ['id' => 'created_by']);
    }
}
