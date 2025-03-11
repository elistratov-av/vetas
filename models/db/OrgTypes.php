<?php

namespace app\models\db;

use app\common\validators\FullTrimValidator;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "public.org_types".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property integer $created_by
 * @property integer $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property bool $is_tech
 */
class OrgTypes extends ActiveRecord
{
    const
        SYSTEM_TYPE_OPERATING = 'operating',
        SYSTEM_TYPE_SHELTER = 'shelter',
        SYSTEM_TYPE_SBBG = 'sbbg',

        SYSTEM_TYPES = [
            self::SYSTEM_TYPE_OPERATING => [
                'name' => 'Управляющая организация',
                'description' => 'Управляющая организация',
            ],
            self::SYSTEM_TYPE_SHELTER => [
                'name' => 'Приют',
                'description' => 'Приют',
            ],
            self::SYSTEM_TYPE_SBBG => [
                'name' => 'СББЖ',
                'description' => 'Станция по борьбе с болезнями животных',
            ],
        ]
    ;

    const SBBJ_ORG_TYPE = 39;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'public.org_types';
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
            [['description'], FullTrimValidator::class],
            [['const'], 'string', 'max' => 50],
            ['is_tech', 'boolean'],
            ['is_tech', 'default', 'value' => false],
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
     * @param bool $noShelters
     * @return array
     */
    public static function options($noShelters = true)
    {
        $query = static::find()
            ->orderBy(['id' => SORT_ASC])
            ->asArray();
        if ($noShelters === true) {
            $query->andWhere(['is_tech' => false]);
        }

        $models = $query->all();

        return ArrayHelper::map($models, 'id', 'name');
    }

    public function beforeSave($insert)
    {
        if ($this->is_system) {
            throw new BadRequestHttpException('Вы не можете сохранить системный тип Организации.');
        }

        return parent::beforeSave($insert);
    }
}
