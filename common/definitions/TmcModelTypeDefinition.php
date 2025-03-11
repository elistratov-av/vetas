<?php

namespace app\common\definitions;

use app\models\db\tmc\TmcBase;
use app\models\db\tmc\TmcDrug;
use app\models\db\tmc\TmcEquipment;
use app\models\db\tmc\TmcExpMaterial;
use app\models\db\tmc\TmcVaccine;

/**
 * Class TmcModelTypeDefinition
 *
 * @package app\common\definitions
 * @author Aleksandr Roik
 */
class TmcModelTypeDefinition extends AbstractDefinition
{

    protected static $collection = [
        TmcBase::TYPE_VACCINE,
        TmcBase::TYPE_DRUG,
        TmcBase::TYPE_EQUIPMENT,
        TmcBase::TYPE_EXP_MATERIAL,
    ];

    /**
     * Названия классов сущностей моделей ТМЦ
     *
     * @var string[]
     */
    protected static $modelCollection = [
        TmcBase::TYPE_VACCINE      => TmcVaccine::class,
        TmcBase::TYPE_DRUG         => TmcDrug::class,
        TmcBase::TYPE_EQUIPMENT    => TmcEquipment::class,
        TmcBase::TYPE_EXP_MATERIAL => TmcExpMaterial::class,
    ];

    /**
     * Возвращает название класса модели сущности ТМЦ по его типу
     *
     * @param $type
     * @return string|null
     */
    public static function getModelByType($type): ?string
    {
        if (!array_key_exists($type, self::$modelCollection)) {
            return null;
        }

        return self::$modelCollection[$type];
    }
}
