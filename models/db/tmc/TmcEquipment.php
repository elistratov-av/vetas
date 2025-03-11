<?php


namespace app\models\db\tmc;


/**
 * Class TmcEquipment
 * @package app\models\db\tmc
 *
 *
 * ЭТИ ПОЛЯ УНАСЛЕДОВАНЫ ОТ ОСНОВНОЙ ЗАПИСИ,
 * НО НЕ ИСПОЛЬЗУЮТСЯ:
 * @property-read  string $basis Основание препарата
 * @property-read string $dealer Представительство
 * @property-read string $excipients Вспомогательные вещества
 * @property-read string $form_description Описание лекарственной формы
 * @property-read int $id_measure Ссылка на справочник единиц измерений
 * @property-read double $unit Содержание активных веществ на ...
 * @property-read string $packaging Упаковка
 * @property-read string $produced Произведено
 * @property-read string $registered Зарегистрировано
 * @property-read  boolean $is_uncountable Неисчислимый расходный материал. Не списывается в приеме
 * ------------
 */
class TmcEquipment extends TmcBase
{

    public function fields()
    {
        return [
            'id',
            'type',
            'name',
            //'basis',
            //'dealer',
            //'description',
            //'excipients',
            //'form_description',
            //'id_measure',
            //'unit',
            //'packaging',
            //'produced',
            //'registered',
            //'is_uncountable',
            'is_deleted',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by'
        ];
    }

    public static function find()
    {
        return parent::find()->andWhere([
            'type' => self::TYPE_EQUIPMENT,
        ]);
    }
}