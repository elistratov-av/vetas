<?php


namespace app\modules\v2\modules\visit\events;


use app\common\models\VisitStatus;
use app\models\db\audit\VisitLog;
use app\models\db\PetOwners;
use app\models\db\Visits;
use app\modules\soap\models\etp\CoordinateStatusMessage1068;
use Yii;
use yii\base\Event;
use yii\db\Exception;
use yii\db\Expression;

class VisitEventHandler {

	const ETP_FUNC_NAME_MOVE_VISIT_BY_OWNER = 'moveVisit';

	const SNAPSHOT_GENERATOR_VERSION = 2,
		API_VERSION = 2;

	const VISITS_LOGS_COLUMNS = [
		'id_visit',
		'status_visit',
		'snapshot',
		'initiator',
		'id_user',
		'fio_user',
		'id_organization',
		'api_version',
		'snapshot_generator_version',
		'date'
	];

	/** @var Visits Прием */
	protected $visit;

	/**
	 * Обработка события
	 *
	 * @param Event $event
	 *
	 * @return bool
	 */
	public function handle(Event $event): bool {
		// --- сохраняем только при смене статуса ---
		if (!array_key_exists('status', $event->changedAttributes)) {
			return true;
		}

		$this->visit = $event->sender;
		$initiator = $this->getInitiator();

		$log = new VisitLog([
			'id_visit'                   => $this->visit->id,
			'status_visit'               => $this->visit->status,
			'snapshot'                   => $this->visit->getAttributes(),
			'initiator'                  => $initiator['type'],
			'id_user'                    => $initiator['id'],
			'fio_user'                   => $initiator['fio'],
			'id_organization'            => $initiator['id_organization'],
			'api_version'                => self::API_VERSION,
			'snapshot_generator_version' => self::SNAPSHOT_GENERATOR_VERSION,
		]);

		return $log->save();
	}

	/**
	 * Обработка события при обновлении многих приемов
	 *
	 * @param VisitEventUpdateMany $event
	 *
	 * @return bool
	 */
	public function handleUpdateMany(VisitEventUpdateMany $event): bool {
		$visits = Visits::find()->where(['IN', 'id', $event->getVisitsId()])->all();
		// задаем один из приемов для определения инициатора изменения статуса приема
		$this->visit = $visits[0];

		try {
			VisitLog::getDb()->createCommand()
				->batchInsert(
					VisitLog::tableName(),
					self::VISITS_LOGS_COLUMNS,
					$this->prepareVisits($visits)
				)
				->execute();
			return true;
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * @return array
	 */
	protected function getInitiator(): array {
		$isCanceledByOwner = $this->visit->status == VisitStatus::CANCELED &&
			$this->visit->cancel_initiator == VisitLog::INITIATOR_OWNER;

		if (Yii::$app->user->getIsGuest()) {
			if ($isCanceledByOwner || self::visitIsChangedByOwner($this->visit->status)) {
				return [
					'type'            => VisitLog::INITIATOR_OWNER_MOS_RU,
					'id'              => $this->visit->id_owner,
					'fio'             => $this->getPetOwnerFullName(),
					'id_organization' => null,
				];
			}

			return [
				'type'            => VisitLog::INITIATOR_SYSTEM,
				'id'              => null,
				'fio'             => null,
				'id_organization' => null,
			];
		} else {
			return [
				'type'            => $isCanceledByOwner ? VisitLog::INITIATOR_OWNER : VisitLog::INITIATOR_CLINIC,
				'id'              => Yii::$app->user->identity->getId(),
				'fio'             => Yii::$app->user->identity->fullname,
				'id_organization' => Yii::$app->user->identity->specialist->id_organization,
			];
		}
	}

	/**
	 * @param Visits[] $visits
	 *
	 * @return array
	 */
	protected function prepareVisits(array $visits): array {
		$rows = [];

		$initiator = $this->getInitiator();
		$time = new Expression('NOW()::timestamp without time zone');

		foreach ($visits as $visit) {
			$rows[] = [
				$visit->id,
				$visit->status,
				$visit->getAttributes(),
				$initiator['type'],
				$initiator['id'],
				$initiator['fio'],
				$initiator['id_organization'],
				self::API_VERSION,
				self::SNAPSHOT_GENERATOR_VERSION,
				$time
			];
		}

		return $rows;
	}

	/**
	 * @return string
	 */
	protected function getPetOwnerFullName(): string {
		return PetOwners::findOne(['id' => $this->visit->id_owner])->fullname;
	}

	/**
	 * Проверка на перенос Приема Владельцем питомца(ев) через MOS.RU
	 *
	 * @param string $visitStatus
	 *
	 * @return bool
	 */
	protected static function visitIsChangedByOwner(string $visitStatus): bool {
		if ($visitStatus != VisitStatus::CHANGED) {
			return false;
		}

		$backtrace = debug_backtrace();
		foreach ($backtrace as $row) {
		    if (!isset($row['class']) || !isset($row['function'])) {
		        continue;
            }
			if ($row['class'] === CoordinateStatusMessage1068::class &&
				$row['function'] === self::ETP_FUNC_NAME_MOVE_VISIT_BY_OWNER) {
				return true;
			}
		}
		return false;
	}
}
