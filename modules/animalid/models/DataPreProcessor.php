<?php


namespace app\modules\animalid\models;


use app\modules\animalid\skeletons\exceptions\IntegrationException;
use yii\helpers\ArrayHelper;

/**
 * DataPreProcessor
 * Помогает отфильтровывать данные
 * или преобразовывать их
 *
 * @package app\modules\animalid\models
 */
class DataPreProcessor
{
    const DIRECTION_IN = 'in';
    const DIRECTION_OUT = 'out';

    const FIELD_KIND_ID = 'KindId';
    const FIELD_BREED_ID = 'BreedId';

    /**
     * Фильтрует данные (по списку фильтров в animalid.filter)
     *
     * @param array $data
     * @param string $direction Направление
     * @return array|null
     * @throws IntegrationException
     */
    public static function filterMessageData($data, $direction)
    {
        self::checkDirection($direction);
        $logCategory = ($direction === self::DIRECTION_IN) ? 'animalid_input' : 'animalid_output';

        /*
         * У нас пока только животные конвертируются
         */
        if ($data['type'] !== 'pets') {
            return $data;
        }

        switch ($direction) {
            case self::DIRECTION_IN:
                $filter = require_once(__DIR__ . '/../rules/filter_in.php');
                break;
            case self::DIRECTION_OUT:
                $filter = require_once(__DIR__ . '/../rules/filter_out.php');
                break;
        }

        /*
         * ID вида и породы
         */
        $kind_id = ArrayHelper::getValue($data, 'data.' . self::FIELD_KIND_ID);
        $breed_id = ArrayHelper::getValue($data, 'data.' . self::FIELD_BREED_ID);

        if (empty($kind_id) || empty($breed_id)) {
            return $data;
        }

        /*
         * Фильтр по породе и виду
         */
        if (array_key_exists($kind_id, $filter) && array_key_exists($breed_id, $filter[$kind_id])) {
            \Yii::info(
                "$data[type]:$data[id] отброшен фильтром",
                $logCategory
            );
            return null;
        }

        /*
         * Фильтр по виду
         */
        if (!empty($filter['species']) && array_key_exists($kind_id, $filter['species'])) {
            \Yii::info(
                "$data[type]:$data[id] отброшен фильтром",
                $logCategory
            );
            return null;
        }

        /*
         * Фильтр по породе
         */
        if (!empty($filter['breeds']) && array_key_exists($breed_id, $filter['breeds'])) {
            \Yii::info(
                "$data[type]:$data[id] отброшен фильтром",
                $logCategory
            );
            return null;
        }

        return $data;
    }

    /**
     * Конвертация данных (по данным animalid.converter)
     *
     * @param $data
     * @param $direction
     * @return mixed
     * @throws IntegrationException
     */
    public static function convertMessageData($data, $direction)
    {
        self::checkDirection($direction);
        $logCategory = ($direction === self::DIRECTION_IN) ? 'animalid_input' : 'animalid_output';

        /*
         * У нас пока только животные конвертируются
         */
        if ($data['type'] !== 'pets') {
            return $data;
        }

        switch ($direction) {
            case self::DIRECTION_IN:
                $converter = require_once(__DIR__ . '/../rules/convert_in.php');
                break;
            case self::DIRECTION_OUT:
                $converter = require_once(__DIR__ . '/../rules/convert_out.php');
                break;
        }

        /*
         * ID вида и породы
         */
        $kind_id = ArrayHelper::getValue($data, 'data.' . self::FIELD_KIND_ID);
        $breed_id = ArrayHelper::getValue($data, 'data.' . self::FIELD_BREED_ID);

        if (empty($kind_id) || empty($breed_id)) {
            return $data;
        }


        if (!array_key_exists($kind_id, $converter) && !array_key_exists($breed_id, $converter[$kind_id])) {
            return $data;
        }

        /*
         * Поля сообщения
         */
        foreach ($data['data'] as $key => $value) {
            $rule = $converter[$kind_id][$breed_id];
            // Есть такое поле в правиле
            if (is_array($rule) && array_key_exists($key, $rule)) {
                ArrayHelper::setValue($data, "data.$key", $rule[$key]);

                \Yii::info(
                    "$data[type]:$data[id] преобразован конвертером $key = $value заменено на $rule[$key]",
                    $logCategory
                );
            }
        }

        return $data;
    }

    /**
     * Проверка верности указания $direction
     * @param $direction
     * @throws IntegrationException
     */
    protected static function checkDirection($direction)
    {
        if (!in_array($direction, [
            self::DIRECTION_IN, self::DIRECTION_OUT
        ])) {
            throw new IntegrationException('При вызове DataPreProcessor указано неизвестное значения для параметра direction');
        }
    }
}
