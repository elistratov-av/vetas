<?php

namespace app\models\db;

use Yii;
use yii\db\ArrayExpression;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use function foo\func;

/**
 * This is the model class for table "fias_addresses".
 *
 * @property int $id
 * @property string $full_address
 * @property string $description
 * @property string $aoguid
 * @property string $region
 * @property integer $regionguid
 * @property string $city
 * @property string $street
 * @property string $house
 * @property string $room
 * @property int $created_by
 * @property int $updated_by
 * @property string $created_at
 * @property string $updated_at
 * @property string $houseguid
 * @property string $roomguid
 * @property string $cityguid
 * @property string $streetguid
 * @property string $text_hash
 * @property int $id_area
 * @property int $id_district
 * @property string $oktmo
 * @property string $lat
 * @property string $lon
 * @property string $coords
 * @property string[] $bti_city_area_code Код административного округа
 * @property PetOwners[] $petOwners
 * @property Areas[] $area
 */
class FiasAddresses extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'fias_addresses';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['full_address', 'description', 'aoguid', 'houseguid', 'roomguid', 'cityguid', 'streetguid'], 'string'],
            [['created_by', 'updated_by'], 'default', 'value' => null],
            [['created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['region', 'city', 'street', 'house', 'room'], 'string', 'max' => 255],
            [['text_hash'], 'string', 'max' => 32],
            [['id_district', 'id_area', 'regionguid'], 'integer'],
            [['bti_city_area_code'], 'each', 'rule' => ['string']],
            [['oktmo'], 'string'],
            [['lat', 'lon'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                 => 'ID',
            'full_address'       => 'Full Address',
            'description'        => 'Description Address',
            'aoguid'             => 'Aoguid',
            'region'             => 'Region',
            'city'               => 'City',
            'street'             => 'Street',
            'house'              => 'House',
            'room'               => 'Room',
            'created_by'         => 'Created By',
            'updated_by'         => 'Updated By',
            'created_at'         => 'Created At',
            'updated_at'         => 'Updated At',
            'regionguid'         => 'Regionguid',
            'houseguid'          => 'Houseguid',
            'roomguid'           => 'Roomguid',
            'cityguid'           => 'Cityguid',
            'streetguid'         => 'Streetguid',
            'text_hash'          => 'Text Hash',
            'id_area'            => 'Id Area',
            'id_district'        => 'Id District',
            'oktmo'              => 'Oktmo',
            'lat'                => 'Lat',
            'lon'                => 'Lon',
            'coords'             => 'Coords',
            'bti_city_area_code' => 'Код административного округа',
        ];
    }

    /**
     * Обновляет существующий по полям
     *      lat, lon, bti_city_area_code, updated_at
     * или создает новый
     * Возвращает id сохраненной, обновленной записи
     *
     * @param $fias_address
     * @return int
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public static function findOrCreateFiasAddress($fias_address)
    {
        $new_fias_address = new FiasAddresses($fias_address);

        // Пытаемся найти уже существующий
        $hash = $new_fias_address->calcHash();
        /* @var $exist \app\models\db\FiasAddresses */
        $exist = FiasAddresses::find()
            ->where(['text_hash' => $hash])
            ->one();

        if (!empty($exist)) {
            $exist->description = ArrayHelper::getValue($fias_address, 'description');
            $exist->update(true, ['description']);

            $lat = ArrayHelper::getValue($fias_address, 'lat');
            $lon = ArrayHelper::getValue($fias_address, 'lon');
            if ($lat != $exist->lat || $lon != $exist->lon) {
                $exist->lat = $lat;
                $exist->lon = $lon;
                $exist->update(true, ['lat', 'lon', 'updated_at']);
            }
            $bti_city_area_code = ArrayHelper::getValue($fias_address, 'bti_city_area_code');
            $bti_city_area_code_exist = (!empty($exist->bti_city_area_code)) ? $exist->bti_city_area_code->getValue() : [];
            if ($bti_city_area_code && $bti_city_area_code_exist) {
                sort($bti_city_area_code);
                sort($bti_city_area_code_exist);

                if ($bti_city_area_code != $bti_city_area_code_exist) {
                    $exist->bti_city_area_code = new ArrayExpression($bti_city_area_code);
                    $exist->update(false, ['bti_city_area_code', 'updated_at']);
                }
            }

            return $exist->id;
        }

        $new_fias_address->save();

        if ($new_fias_address->hasErrors()) {
            $errors = $new_fias_address->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка:' : implode("\n", array_values($errors)));
        }

        return $new_fias_address->id;
    }

    /**
     * Сохраняет адрес
     *
     * @param $attributes
     * @return FiasAddresses
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public static function createOrUpdateFiasAddress(?int $id, array $attributes, $withOutFields = [])
    {
        $fiasAddress = $id ? FiasAddresses::findOne($id)->setAttributes($attributes) : new FiasAddresses($attributes);
        $attributeNames = [];

        if ($withOutFields) {
            $attributeNames = array_filter(array_keys(
                $fiasAddress->getAttributes()),
                function ($key) use ($withOutFields) {
                    return !in_array($key, $withOutFields);
                });
        }

        $fiasAddress->save(true, $attributeNames);

        if ($fiasAddress->hasErrors()) {
            $errors = $fiasAddress->getErrorSummary(true);
            throw new BadRequestHttpException(empty($errors) ? 'Ошибка:' : implode("\n", array_values($errors)));
        }

        return $fiasAddress;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPetOwners()
    {
        return $this->hasMany(PetOwners::class, ['id_fact_fias_address' => 'id']);
    }

    public function beforeSave($insert)
    {
        // Высчитываем хеш
        $this->text_hash = $this->calcHash();

        if ($this->oktmo) {
            $this->id_district = $this->getDistrictFromOKTMO($this->oktmo);
            $this->id_area = $this->getAreaFromOKTMO($this->oktmo);
        }

        return parent::beforeSave($insert);
    }

    /**
     * Высчитывает хеш от строковых полей модели
     *
     * @return string
     */
    public function calcHash()
    {
        $array = [
            $this->aoguid,
            $this->region,
            $this->city,
            $this->street,
            $this->house,
            $this->room,
            $this->houseguid,
            $this->roomguid,
            $this->cityguid,
            $this->streetguid,
            $this->regionguid,
            $this->description,
        ];

        return mb_strtoupper(
            md5(
                implode('|', $array
                )
            ));
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getArea()
    {
        return $this->hasOne(Areas::class, ['id' => 'id_area']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDistrict()
    {
        return $this->hasOne(Districts::class, ['id' => 'id_district']);
    }

    private function getAreaFromOKTMO($oktmo)
    {
        $result = Districts::findOne(['oktmo' => $oktmo]);

        return !empty($result) ? $result->id_area : null;
    }

    private function getDistrictFromOKTMO($oktmo)
    {
        $result = Districts::findOne(['oktmo' => $oktmo]);

        return !empty($result) ? $result->id : null;
    }
}
