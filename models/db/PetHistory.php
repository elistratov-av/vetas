<?php

namespace app\models\db;

use Yii;
use yii\web\ForbiddenHttpException;

/**
 * This is the model class for table "pet_health".
 *
 * @property int $id
 * @property int $id_pet
 * @property string $event
 * @property int $options
 * @property int $id_organization
 * @property int $created_by
 * @property string $created_at
 * @property string $options_str
 *
 * @property Pets $pet
 * @property Organizations $organization
 * @property Users $creater
 */
class PetHistory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pet_history';
    }

    const HISTORY_EVENT_REGISTERED = 'REGISTERED';
    const HISTORY_EVENT_SHELTER = 'SHELTER';
    const HISTORY_EVENT_QUARANTINE = 'QUARANTINE';
    const HISTORY_EVENT_QUARANTINE_OTHER = 'QUARANTINE_OTHER';
    const HISTORY_EVENT_QUARANTINE_END = 'QUARANTINE_END';
    const HISTORY_EVENT_CASTRATED = 'CASTRATED';
    const HISTORY_EVENT_MAINTENANCE = 'MAINTENANCE';
    const HISTORY_EVENT_AVIARY = 'AVIARY';
    const HISTORY_EVENT_VACCINATION = 'VACCINATION';
    const HISTORY_EVENT_TREATMENT = 'TREATMENT';
    const HISTORY_EVENT_RETURN_PET = 'RETURN_PET';
    const HISTORY_EVENT_NEW_OWNER = 'NEW_OWNER';
    const HISTORY_EVENT_EUTHANASIA = 'EUTHANAZIA';
    const HISTORY_EVENT_DEATH = 'DEATH';
    const HISTORY_EVENT_ESCAPE = 'ESCAPE';
    const HISTORY_EVENT_DEPARTURE = 'DEPARTURE';
    const HISTORY_EVENT_DEREGISTERED = 'DEREGISTERED';

    const HISTORY_EVENTS = [
        self::HISTORY_EVENT_REGISTERED => 'Зарегистрировано в системе',
        self::HISTORY_EVENT_SHELTER => 'Поступило в приют',
        self::HISTORY_EVENT_QUARANTINE => 'Помещено в карантин',
        self::HISTORY_EVENT_QUARANTINE_OTHER => 'Карантин продлён',
        self::HISTORY_EVENT_QUARANTINE_END => 'Карантин закончен',
        self::HISTORY_EVENT_CASTRATED => 'Стерилизация / кастрация',
        self::HISTORY_EVENT_MAINTENANCE => 'Размещено в приюте на содержание',
        self::HISTORY_EVENT_AVIARY => 'Помещено в вольер',
        self::HISTORY_EVENT_VACCINATION => 'Вакцинация',
        self::HISTORY_EVENT_TREATMENT => 'Обработка',
        self::HISTORY_EVENT_RETURN_PET => 'Возврат в приют',
        self::HISTORY_EVENT_NEW_OWNER => 'Новый хозяин',
        self::HISTORY_EVENT_EUTHANASIA => 'Эвтаназия',
        self::HISTORY_EVENT_DEATH => 'Падёж',
        self::HISTORY_EVENT_ESCAPE => 'Побег',
        self::HISTORY_EVENT_DEPARTURE => 'Выбытие',
        self::HISTORY_EVENT_DEREGISTERED => 'Снято с учёта',
    ];

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_pet', 'event', 'id_organization', 'created_by', 'created_at'], 'required'],
            [['id_organization', 'id_pet', 'options', 'created_by'], 'integer'],
            [['created_at'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
            [['event', 'options_str'], 'string', 'max' => 255],
            ['id_organization', 'exist', 'skipOnError' => true, 'targetClass' => Organizations::class, 'targetAttribute' => ['id_organization' => 'id']],
            ['id_pet', 'exist', 'skipOnError' => true, 'targetClass' => Pets::class, 'targetAttribute' => ['id_pet' => 'id']],
            ['created_by', 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['created_by' => 'id']],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPet()
    {
        return $this->hasOne(Pets::class, ['id' => 'id_pet']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrganization()
    {
        return $this->hasOne(Organizations::class, ['id' => 'id_organization']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreater()
    {
        return $this->hasOne(Users::class, ['id' => 'created_by']);
    }

    public static function addRecord(array $attributes)
    {
        if ($user = Yii::$app->user->getIdentity()) {

            $defaul_attributes = [
                'id_organization' => $user->specialist->id_organization,
                'created_by' => $user->getId(),
                'created_at' => Date("Y-m-d H:i:s"),
            ];

            $model = new static();
            $model->setAttributes(array_merge($defaul_attributes, $attributes));
            $model->save();

            return $model;
        }

        throw new ForbiddenHttpException('Недостаточно прав доступа');

    }

}
