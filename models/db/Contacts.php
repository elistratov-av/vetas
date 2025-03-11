<?php

namespace app\models\db;

use app\common\components\inform\SpkService;
use app\models\db\subscription\Subscriptions;
use Yii;
use yii\validators\EmailValidator;

/**
 * This is the model class for table "contacts".
 *
 * @property int $id
 * @property int $id_contact_type
 * @property string $entity_type
 * @property int $entity_id
 * @property string $name
 * @property int $created_by Автор добавления (id пользователя)
 * @property int $updated_by Автор последнего изменения (id пользователя)
 * @property string $created_at Дата создания
 * @property string $updated_at Дата изменения
 * @property bool $main_flag Флаг: основной контакт
 * @property bool $confirmed Контакт подтвержден
 *
 * @property ContactTypes $contactType
 * @property ContactTypes $contact_type
 * @property RegCertificates[] $regCertificates
 * @property RegCertificates[] $regCertificates0
 * @property Subscriptions[] $subscriptions
 */
class Contacts extends ActiveRecord
{
    const ENTITY_TYPE_PET_OWNER = 'pet_owner';
    const ENTITY_TYPE_ORGANIZATION = 'organization';

    const PHONE_REG_EXP = '/^(\+7\d{10})$/';
    /**
     * @var mixed|string|null
     */
    public $message;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'contacts';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_contact_type', 'entity_type', 'entity_id', 'name'], 'required'],
            [['id_contact_type', 'entity_id', 'created_by', 'updated_by'], 'default', 'value' => null],
            [['id_contact_type', 'entity_id', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['main_flag', 'confirmed'], 'boolean'],
            [['entity_type'], 'string', 'max' => 50],
            [['name'], 'string', 'max' => 255],
            [
                ['id_contact_type'], 'exist', 'skipOnError' => true,
                'targetClass' => ContactTypes::class,
                'targetAttribute' => ['id_contact_type' => 'id']
            ],
            ['name', 'match', 'pattern' => self::PHONE_REG_EXP, 'when' => function () {
                return $this->contactType->type == ContactTypes::TYPE_PHONE;
            }, 'message' => 'Телефон должен быть в формате +71234567890'],
            ['name', 'email', 'when' => function () {
                return $this->contactType->type == ContactTypes::TYPE_EMAIL;
            }, 'message' => 'Введенный email имеет ошибочный формат'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_contact_type' => 'Id Contact Type',
            'entity_type' => 'Entity Type',
            'entity_id' => 'Entity ID',
            'name' => 'Name',
            'created_by' => 'Автор добавления (id пользователя)',
            'updated_by' => 'Автор последнего изменения (id пользователя)',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата изменения',
            'main_flag' => 'Флаг: основной контакт',
            'confirmed' => 'Контакт подтвержден',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContactType()
    {
        return $this->hasOne(ContactTypes::class, ['id' => 'id_contact_type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContact_type()
    {
        return $this->hasOne(ContactTypes::class, ['id' => 'id_contact_type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegCertificates()
    {
        return $this->hasMany(RegCertificates::class, ['phone' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegCertificates0()
    {
        return $this->hasMany(RegCertificates::class, ['mail' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriptions()
    {
        return $this->hasMany(Subscriptions::class, ['id_contact' => 'id']);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getTypeForInformation()
    {
        switch ($this->contact_type->type) {
            case ContactTypes::TYPE_PHONE:
                return SpkService::CHANNEL_MSISDN;

            case ContactTypes::TYPE_EMAIL:
                return SpkService::CHANNEL_EMAIL;

            default:
                throw new \Exception("Недопустимый тип контакта для уведомлений");
        }
    }
}
