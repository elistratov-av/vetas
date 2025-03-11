<?php


namespace app\modules\v2\modules\gosvetnadzor\cron;

use app\models\db\Diseases;
use app\models\db\ViolationType;
use yii\base\InvalidConfigException;

/**
 * Базовый класс проверки нарушений
 *
 * @package app\modules\v2\modules\gosvetnadzor\cron
 */
abstract class BaseViolationCheck
{
    /**
     * Возвращает id заболевания по имени или генерирует исключение (если не найдено)
     *
     * @param $disease_name
     * @return int
     * @throws InvalidConfigException
     */
    protected function getDiseaseId($disease_name)
    {
        /** @var Diseases $disease */
        $disease = Diseases::find()
            ->select('id')
            ->where(['name' => $disease_name])
            ->one();

        if (empty($disease)) {
            throw new InvalidConfigException('Заболевание с названием ' . $disease_name . ' не найдено - обратитесь к разработчику');
        }

        return $disease->id;
    }

    /**
     * Возвращает id типа нарушения или генерирует исключение (если не найдено)
     *
     * @param $violation_type_tech_name
     * @return int
     * @throws InvalidConfigException
     */
    protected function getViolationTypeId($violation_type_tech_name)
    {
        /** @var ViolationType $violation_type */
        $violation_type = ViolationType::find()
            ->select('id_type')
            ->where(['tech_name' => $violation_type_tech_name])
            ->one();

        if (empty($violation_type)) {
            throw new InvalidConfigException('Тип нарушения ' . $violation_type_tech_name . ' не найден - обратитесь к разработчику');
        }

        return $violation_type->id_type;
    }
}
