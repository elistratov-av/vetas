<?php

namespace app\modules\adminv\models\forms;

use app\common\efsp\EfspWrapper;
use app\models\db\Areas;
use app\models\db\Districts;
use app\models\db\FiasAddresses;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class OrganizationAddressForm
 * @package app\modules\adminv\models
 */
class OrganizationAddressForm extends Model
{
    /**
     * @var array
     */
    private $fieldsToClear = [
        'id_area',
        'id_district',
        'region',
        'regionguid',
        'city',
        'cityguid',
        'street',
        'streetguid',
        'house',
        'houseguid',
        'room',
        'roomguid',
        'oktmo'
    ];
    /**
     * @var int
     */
    public $id_area;
    /**
     * @var int
     */
    public $id_district;
    /**
     * @var int
     */
    public $id_fias_address;
    /**
     * @var string
     */
    public $region;
    /**
     * @var string
     */
    public $regionguid;
    /**
     * @var string
     */
    public $regionfias;
    /**
     * @var string
     */
    public $city;
    /**
     * @var string
     */
    public $cityguid;
    /**
     * @var string
     */
    public $street;
    /**
     * @var string
     */
    public $streetguid;
    /**
     * @var string
     */
    public $house;
    /**
     * @var string
     */
    public $houseguid;
    /**
     * @var string
     */
    public $room;
    /**
     * @var string
     */
    public $roomguid;
    /**
     * @var string
     */
    public $oktmo;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['id_area', 'id_district', 'id_fias_address'], 'integer'],
            ['id_area', 'exist', 'skipOnError' => true, 'targetClass' => Areas::class, 'targetAttribute' => ['id_area' => 'id']],
            ['id_district', 'exist', 'skipOnError' => true, 'targetClass' => Districts::class, 'targetAttribute' => ['id_district' => 'id']],
            ['id_fias_address', 'exist', 'skipOnError' => true, 'targetClass' => FiasAddresses::class, 'targetAttribute' => ['id_fias_address' => 'id']],
            [['region', 'regionguid', 'city', 'cityguid', 'street', 'streetguid', 'house', 'houseguid', 'room', 'roomguid', 'oktmo'], 'string'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
            'id_area' => 'Округ',
            'id_district' => 'Район',
            'region' => 'Регион',
            'city' => 'Город',
            'street' => 'Улица',
            'house' => 'Дом',
            'room' => 'Квартира',
            'oktmo' => 'ОКТМО',
            'regionguid' => 'Регион',
            'cityguid' => 'Город',
            'streetguid' => 'Улица',
            'houseguid' => 'Дом',
            'roomguid' => 'Квартира',
        ];
    }

    /**
     * @return array
     */
    public static function areasOptions()
    {
        $models = Areas::find()
            ->orderBy(['name' => SORT_ASC])
            ->asArray()
            ->all();

        return ArrayHelper::map($models, 'id', 'name');
    }

    /**
     * @return array
     */
    public static function districtsOptions()
    {
        $models = Districts::find()
            ->orderBy(['name' => SORT_ASC])
            ->asArray()
            ->all();

        return ArrayHelper::map($models, 'id', 'name');
    }

    /**
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @throws \yii\web\BadRequestHttpException
     */
    public function saveFiasAddress()
    {
        $address = $this->prepareToFiasAddress($this->toArray());

        $fiasAddressId = FiasAddresses::findOrCreateFiasAddress($address);
        $this->id_fias_address = $fiasAddressId;
    }

    /**
     *
     */
    public function loadFiasAddress()
    {
        if (!empty($this->id_fias_address)) {
            $model = FiasAddresses::findOne(['id' => (int)$this->id_fias_address]);
            if ($model !== null) {
                $this->load($model->toArray(), '');
            }
        }
    }

    /**
     * Реформатируем ответ под модель fiasAddress
     * Так же проверяем координаты с fias если указан houseguid
     *
     * @param array $formAsArray
     * @return array
     */
    private function prepareToFiasAddress(array $formAsArray)
    {
        if ($formAsArray['houseguid'] !== '' && $formAsArray['houseguid'] !== null) {
            $response = (new EfspWrapper())->getAddress($formAsArray['houseguid']);
            $polygon = $response['suggestions'][0]['data']['polygon'];
            if ($polygon !== null) {
                $coordinates = $polygon['coordinates'][0][0];
                $count = count($coordinates);
                $lon = 0;
                $lat = 0;
                foreach ($coordinates as $coordinate) {
                    $lon += $coordinate[0];
                    $lat += $coordinate[1];
                }
                $formAsArray['lon'] = $lon / $count;
                $formAsArray['lat'] = $lat / $count;
            }
        }
//        if ($formAsArray['id_fias_address'] !== null && $formAsArray['id_fias_address'] !== '') $formAsArray['id'] = $formAsArray['id_fias_address'];
        unset($formAsArray['id_fias_address']);
        // Поле regionfias нам нужно для корректной работы фильтра в form.php, но в самой модели fiasAddress нам нужен только regionguid
        unset($formAsArray['regionfias']);

        return $this->clearEmptyStringValues($formAsArray);
    }

    /**
     * Если какое либо из полей адреса не выбрано - ставим null вместо пустой строки
     *
     * @param array $formAsArray
     * @return array
     */
    private function clearEmptyStringValues(array $formAsArray): array
    {
        foreach($formAsArray as $key => $attribute) {
            if (in_array($key, $this->fieldsToClear) && $attribute === '') {
                $formAsArray[$key] = null;
            }
        }
        return $formAsArray;
    }
}
