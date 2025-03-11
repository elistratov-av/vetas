<?php


namespace app\modules\v2\modules\visit\events;


use app\models\db\Visits;
use yii\base\Event;

class VisitEventUpdateMany extends Event {

	/** @var array */
	protected $visitsId;

	/**
	 * @param Visits[] $visits
	 *
	 * @return $this
	 */
	public function setVisitsId(array $visits): self {
		$this->visitsId = array_map(
			function(Visits $visit) {
				return $visit->id;
			},
			$visits
		);
		return $this;
	}

	/**
	 * @return array
	 */
	public function getVisitsId(): array {
		return $this->visitsId;
	}

}