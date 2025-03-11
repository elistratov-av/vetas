<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 29.07.19
 * Time: 14:00
 */

namespace app\modules\v2\modules\changeRequest\helpers;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class RequestEntitiesHelper
{
    /**
     * @return array
     */
    public static function entityList(): array
    {
        return [
            'orgs/types' => 'Справочник типов организаций',
            'units' => 'Справочник единиц измерения',
            'deregistration' => 'Справочник причин снятия с регистрационного учета',
            'active-substances' => 'Справочник активных веществ',
            'drugs' => 'Справочник препаратов',
            'equipments' => 'Справочник оборудования',
            'exp-materials' => 'Справочник расходных материалов',
            'animals/types' => 'Справочник видов животных',
            'diseases' => 'Справочник заболеваний',
        ];
    }

    /**
     * @return array
     */
    public static function typeList(): array
    {
        return [
            'C' => 'Создание',
            'U' => 'Редактирование',
            'D' => 'Удаление',
        ];
    }

    /**
     * @return array
     */
    public static function stateList(): array
    {
        return [
            'N' => 'Новый',
            'A' => 'Принят',
            'D' => 'Отклонен',
        ];
    }

    /**
     * @param $entity_name
     * @return string
     */
    public static function entityName($entity_name): string
    {
        return ArrayHelper::getValue(self::entityList(), $entity_name);
    }

    /**
     * @param $type
     * @return string
     */
    public static function typeName($type): string
    {
        return ArrayHelper::getValue(self::typeList(), $type);
    }

    /**
     * @param $state
     * @return string
     */
    public static function stateName($state): string
    {
        return ArrayHelper::getValue(self::stateList(), $state);
    }

    /**
     * @param $entity_name
     * @return string
     */
    public static function entityLabel($entity_name): string
    {
        return Html::tag('span', ArrayHelper::getValue(self::entityList(), $entity_name));
    }

    /**
     * @param $type
     * @return string
     */
    public static function typeLabel($type): string
    {
        return Html::tag('span', ArrayHelper::getValue(self::typeList(), $type));
    }

    /**
     * @param $state
     * @return string
     */
    public static function stateLabel($state): string
    {
        return Html::tag('span', ArrayHelper::getValue(self::stateList(), $state));
    }
}
