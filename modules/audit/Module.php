<?php

namespace app\modules\audit;

use app\models\db\Pets;
use app\modules\audit\models\AuditEventHandler;
use yii\base\Event;
use yii\base\Module as YiiModule;
use yii\db\ActiveRecord;

/**
 * Модуль аудита
 *
 * @package app\modules\audit
 */
class Module extends YiiModule
{
    public $controllerNamespace = 'app\modules\audit\controllers';

    public function init()
    {
        parent::init();

        \Yii::debug('INIT AUDIT', 'audit');

        /*
         * Вставка
         */
        Event::on(
            ActiveRecord::class,
            ActiveRecord::EVENT_AFTER_INSERT,
            function ($event) {
                (new AuditEventHandler())->handle($event);
            }
        );

        /*
         * Обновление
         */
        Event::on(
            ActiveRecord::class,
            ActiveRecord::EVENT_AFTER_UPDATE,
            function ($event) {
                (new AuditEventHandler())->handle($event);
            }
        );

        /*
         * ПЕРЕД УДАЛЕНИЕМ (для родительских сущностей)
         */
        Event::on(
            ActiveRecord::class,
            ActiveRecord::EVENT_BEFORE_DELETE,
            function ($event) {
                (new AuditEventHandler())->handle($event);
            }
        );

        /*
         * ПОСЛЕ УДАЛЕНИЯ (для дочерних сущностей)
         */
        Event::on(
            ActiveRecord::class,
            ActiveRecord::EVENT_AFTER_DELETE,
            function ($event) {
                (new AuditEventHandler())->handle($event);
            }
        );

        /*
         * После массового сохранения вакцинаций
         */
        Event::on(
            ActiveRecord::class,
            Pets::EVENT_AFTER_SAVE_VACCINATION,
            function ($event) {
                (new AuditEventHandler())->handlePetsVaccination($event);
            }
        );
    }
}
