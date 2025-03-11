<?php

namespace app\models\db;

/**
 * Class AbstractPetTmc
 *
 * @property int $id
 * @property int $id_pet
 * @property string $type_tmc
 * @property string $drug_name Наименование вакцины
 * @property string $producer_name
 * @property string $date Дата вакцинации
 * @property int $id_organization
 * @property int $id_specialist
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @package app\models\db
 * @author Aleksandr Roik
 */
abstract class AbstractPetTmc extends ActiveRecord
{
    /**
     * Возвращает название звязанного id поля ТМЦ
     *
     * @return string
     */
    abstract public function getTmcFieldName(): string;

    /**
     * Возвращает значение звязанного id поля ТМЦ
     *
     * @return int
     */
    abstract public function getTmc(): int;
}
