<?php

namespace app\modules\v2\modules\visit;

use app\models\db\Visits;
use app\modules\v2\modules\visit\events\VisitEventHandler;
use yii\base\Event;
use yii\base\Module as YiiModule;

/**
 * Class Module
 * @package app\modules\v2\modules\visit
 */
class Module extends YiiModule
{
    public $controllerNamespace = 'app\modules\v2\modules\visit\controllers';

    public function init() {
		parent::init();

		// ---------------- INSERT -------------------------------------------
		Event::on(
			Visits::class,
			Visits::EVENT_AFTER_INSERT,
			function ($event) {
				(new VisitEventHandler())->handle($event);
			}
		);

		// ---------------- UPDATE -------------------------------------------
		/*
			update вызывает updateAll
			ActiveRecord->update:625 ($this->updateInternal)
			BaseActiveRecord->updateInternal:802 (static::updateAll)
		 */

		// ---------------- UPDATE_ALL ---------------------------------------
		// Visits::updateAll
	}
}
