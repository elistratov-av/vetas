<?php

namespace app\models\db\found_pet;

use app\models\db\ActiveRecord;

/**
 * Class AdAddress
 * @package app\models\db\found_pet
 *
 * @property int     $id                    Идентификатор
 * @property string  $city                  Город
 * @property string  $settlement            Населенный пункт
 * @property string  $area                  Район
 * @property string  $street                Улица
 * @property string  $house                 Номер дома
 * @property string  $postcode              Почтовый индекс
 * @property string  $geo_lat               Широта
 * @property string  $geo_lon               Долгота
 * @property int     $radius                Радиус поиска
 * @property string  $fias_id               Идентификатор адреса ФИАС
 * @property string  $kladr_id              Идентификатор адреса КЛАДР
 * @property boolean $is_map                Признак ввода по координатам карты
 * @property boolean $is_manually_set       Признак ручного ввода данных
 * @property string  $pobox                 Полный адрес, city + settlement + area + street
 * @property string  $created_at
 * @property string  $updated_at
 */
class AdAddress extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'found_pet.ad_addresses';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_map', 'is_manually_set'], 'required'],
            [['is_map', 'is_manually_set'], 'boolean'],
            [['radius'], 'integer'],
            [['city', 'settlement', 'area', 'street', 'house', 'postcode', 'geo_lat', 'geo_lon', 'fias_id', 'kladr_id', 'pobox'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }
}
