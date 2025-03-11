<?php


namespace app\models\db\audit;


use app\models\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "audit.visits_logs"
 *
 * @property int    $id                         ID
 * @property int    $id_visit                   ID приема
 * @property string $status_visit               Статус приема
 * @property array  $snapshot                   Состояние после обновления
 * @property string $initiator                  Инициатор смены статуса приема
 * @property int    $id_user                    ID пользователя, изменивший статус приема
 * @property string $fio_user                   ФИО пользователя, изменивший статус приема
 * @property string $id_organization            ID организации пользователя, изменивший статус приема
 * @property string $date                       Дата и время изменения данных
 * @property int    $api_version                Версия API
 * @property int    $snapshot_generator_version Версия создателя снимков
 */
class VisitLog extends ActiveRecord {

	const
		INITIATOR_SYSTEM = 'SYSTEM',
		INITIATOR_CLINIC = 'CLINIC',
		INITIATOR_OWNER = 'OWNER',
		INITIATOR_OWNER_MOS_RU = 'OWNER_MOS_RU';

	public function behaviors()
	{
		return [
			[
				'class' => TimestampBehavior::class,
				'createdAtAttribute' => 'date',
				'updatedAtAttribute' => false,
				'value' => new Expression('NOW()::timestamp without time zone'),
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public static function tableName() {
		return 'audit.visits_logs';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules() {
		return [
			[['id_user', 'fio_user', 'id_organization'], 'default', 'value' => null],
			[['id', 'id_visit', 'id_user', 'api_version', 'snapshot_generator_version', 'id_organization'], 'integer'],
			[['date', 'snapshot'], 'safe'],
			[['id_visit', 'status_visit', 'initiator'], 'required'],
			[['status_visit'], 'string', 'max' => 1],
			[['initiator'], 'string', 'max' => 12],
			['initiator', 'in', 'range' => [
				self::INITIATOR_CLINIC, self::INITIATOR_OWNER, self::INITIATOR_SYSTEM, self::INITIATOR_OWNER_MOS_RU
			]],
			['initiator', 'string']
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels() {
		return [
			 'id' => 'ID',
			 'id_visit' => 'ID визита',
			 'status_visit' => 'Статус визита',
			 'snapshot' => 'Состояние после обновления',
			 'initiator' => 'Инициатор смены статуса визита',
			 'id_user' => 'ID пользователя, изменивший статус визита',
			 'fio_user' => 'ФИО пользователя, изменивший статус визита',
			 'id_organization' => 'ID организации пользователя, изменивший статус приема',
			 'date' => 'Дата и время изменения данных',
			 'api_version' => 'Версия API',
			 'snapshot_generator_version' => 'Версия создателя снимков',
		];
	}
}