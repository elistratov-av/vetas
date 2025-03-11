<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 25.07.19
 * Time: 16:20
 */

namespace app\models\db;

use app\common\validators\FullTrimValidator;

/**
 * Class ChangeRequest
 * @package app\models\db
 *
 * @property int    $id
 * @property string $entity_name
 * @property int    $author
 * @property int    $author_org
 * @property int    $admin
 * @property string $type
 * @property string $description
 * @property string $state
 * @property string $created_at
 * @property string $updated_at
 *
 * @property \app\models\db\Organizations $organization
 * @property \app\models\db\Users         $requestAuthor
 * @property \app\models\db\Users         $processedAdmin
 *
 */
class ChangeRequest extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'change_request';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['entity_name', 'author', 'author_org', 'type', 'description'], 'required'],
            [['admin'], 'default', 'value' => null],
            [['description'], 'string'],
            [['description'], FullTrimValidator::class],
            [['description'], 'default', 'value' => 'Описание отсутствует'],
            [['author', 'author_org', 'admin'], 'integer'],
            [['entity_name'], 'string', 'max' => 255],
            [['type', 'state'], 'string', 'max' => 1],
            ['type', 'in', 'range' => ['C', 'U', 'D'], 'strict' => true, 'allowArray' => false],
            ['entity_name',
                'in',
                'range' => [
                    'orgs/types',
                    'units',
                    'deregistration',
                    'active-substances',
                    'drugs',
                    'equipments',
                    'exp-materials',
                    'animals/types',
                    'diseases'
                ],
                'strict' => true,
                'allowArray' => false
            ],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'entity_name' => 'Наименование сущности справочника',
            'author' => 'Автор запроса',
            'author_org' => 'Организация автора запроса',
            'admin' => 'Обработавший заявку',
            'type' => 'Тип заявки',
            'description' => 'Описание',
            'state' => 'Состояние заявки',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organizations::class, ['id' => 'author_org']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRequestAuthor()
    {
        return $this->hasOne(Users::class, ['id' => 'author']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProcessedAdmin()
    {
        return $this->hasOne(Users::class, ['id' => 'admin']);
    }
}
