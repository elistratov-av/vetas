<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 05.12.18
 * Time: 14:16
 */

namespace app\modules\admin\helpers;

use app\common\models\VisitStatus;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Class VisitStatusHelper
 * @package app\modules\admin\helpers
 */
class VisitStatusHelper
{
    /**
     * @return array
     */
    public static function statusList(): array
    {
        return [
            VisitStatus::NEW => 'Новый',
            VisitStatus::CHANGED => 'Изменен',
            VisitStatus::IN_WORK => 'В работе',
            VisitStatus::CANCELED => 'Отменен',
            VisitStatus::FINISHED => 'Завершен',
            VisitStatus::TRANSFER => 'Перенесен',
            VisitStatus::TIMEOUT => 'Пациент не явился',
        ];
    }

    /**
     * @param $status
     * @return string
     */
    public static function statusName($status): string
    {
        return ArrayHelper::getValue(self::statusList(), $status);
    }

    /**
     * @param $status
     * @return string
     */
    public static function statusLabel($status): string
    {
        return Html::tag('span', ArrayHelper::getValue(self::statusList(), $status));
    }

}