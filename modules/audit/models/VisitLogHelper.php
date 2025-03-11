<?php


namespace app\modules\audit\models;


use app\common\models\VisitStatus;
use app\models\db\audit\VisitLog;
use app\models\db\ShiftType;
use app\models\db\Visits;
use app\modules\admin\helpers\VisitStatusHelper;

class VisitLogHelper {

	protected static $description_template = [
		VisitStatus::NEW      => 'создан',
		VisitStatus::CHANGED  => 'изменен',
		VisitStatus::IN_WORK  => 'взят в работу',
		VisitStatus::CANCELED => 'отменен',
		VisitStatus::FINISHED => 'завершен',
		VisitStatus::TIMEOUT  => 'отложен',
		VisitStatus::TRANSFER => 'назначен к переносу',
	];

	protected static $initiator_text = [
		VisitLog::INITIATOR_OWNER => 'Владелец животного',
		VisitLog::INITIATOR_CLINIC => 'Пользователь',
		VisitLog::INITIATOR_SYSTEM => 'Система',
		VisitLog::INITIATOR_OWNER_MOS_RU => 'Владелец животного (mos.ru)'
	];

	/**
	 * @param string $visitStatus
	 *
	 * @return string
	 */
	public static function getStatusText(string $visitStatus): string {
		return VisitStatusHelper::statusLabel($visitStatus);
	}

	/**
	 * @param string $initiator
	 *
	 * @return string
	 */
	public static function getInitiatorText(string $initiator): string {
		return self::$initiator_text[$initiator];
	}

	/**
	 * @param VisitLog $visit_log_record
	 *
	 * @return string
	 */
	public static function getDescription(VisitLog $visit_log_record): string {
		if (array_key_exists($visit_log_record->status_visit, self::$description_template)) {
			switch ($visit_log_record->initiator) {
				case VisitLog::INITIATOR_SYSTEM:
					return 'Прием ' . self::$description_template[$visit_log_record->status_visit] . ' системой';
				case VisitLog::INITIATOR_CLINIC:
					return 'Прием ' . self::$description_template[$visit_log_record->status_visit] . ' пользователем ' . $visit_log_record->fio_user;
				case VisitLog::INITIATOR_OWNER:
					return 'Прием ' . self::$description_template[$visit_log_record->status_visit] . ' владельцем животного';
				case VisitLog::INITIATOR_OWNER_MOS_RU:
					return 'Прием ' . self::$description_template[$visit_log_record->status_visit] . ' владельцем животного (через mos.ru)';
				default:
					return 'ОШИБКА: лог приема был сохранен неверно, или возникла ошибка при выводе';
			}
		} else {
			return 'ОШИБКА: лог приема был сохранен неверно, или возникла ошибка при выводе';
		}
	}

	/**
	 * @param array $snapshot
	 *
	 * @return array
	 */
	public static function prepareVisitSnapshot($snapshot): array {
		if (!is_array($snapshot)) {
			return $snapshot;
		}

		$snapshot['time_range'] = $snapshot['time_range'] ?
			self::prepareTsrange($snapshot['time_range'])
			: null;
		$snapshot['time_range_without_cooldown'] = $snapshot['time_range_without_cooldown'] ?
			self::prepareTsrange($snapshot['time_range_without_cooldown'])
			: null;

		// Прячем пустые поля
		foreach ($snapshot as $name => $value) {
			if (is_null($value) || (is_string($value) && empty($value))) {
				unset($snapshot[$name]);
			}
		}

		$snapshot['type'] = Visits::typeOptions()[$snapshot['type']];

		$snapshot['channel'] = ShiftType::find()
			->select('description')
			->where(['=', 'id', $snapshot['channel']])
			->one()->description;

		return $snapshot;
	}

	/**
	 * @param string $tsrange
	 *
	 * @return string
	 */
	protected static function prepareTsrange(string $tsrange): string {
		return preg_replace(['/\["/', '/","/', '/"\)/'], ['', ' - ', ''], $tsrange);
	}

}