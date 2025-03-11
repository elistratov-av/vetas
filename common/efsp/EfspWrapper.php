<?php


namespace app\common\efsp;

class EfspWrapper
{
    /**
     * ФИАС код для региона Москвы (надеемся что он не измениться)
     */
    const MOSCOW_FIAS_CODE = '0c5b2444-70a0-4932-980c-b4dc0d3f02b5';

    /**
     * ФИАС код для города Москвы
     */
    const MOSCOW_CITY_FIAS_CODE = 'fbcf1fff-1d7c-445e-ad92-b71c08b8aba3';

    /**
     * @var \app\common\efsp\Client
     */
    protected $client;

    public function __construct()
    {
        // TODO:DI
        $this->client = \Yii::$app->authClients->getClient('efsp');
    }

    protected function getToken()
    {
        /* $token =  */$this->client->authenticateClient();
    }

    /**
     * Получить список округов Москвы
     * @return array|false
     */
    public function getMoscowAdmDistrictsArray()
    {
        $districts = $this->getDistricts(self::MOSCOW_FIAS_CODE);

        if (empty($districts['level2']) || !is_array($districts['level2'])) {
            return FALSE;
        }

        $result = [];
        foreach ($districts['level2'] as $district) {
            $result[$district['code']] = $district['name'] . ' ' . $district['type'];
        }

        return $result;
    }

    /**
     * @param string $fias_code
     * @return array
     */
    public function getDistricts($fias_code)
    {
        $subUrl = '/districts?fias_id=' . $fias_code;
        $this->getToken();
        return $this->client->api($subUrl);
    }

    /**
     * @param string $fiasCode
     * 
     * @return array|null [latitude, longitude]
     * 
     * TODO:efsp-apis Перенести в отдельный компонент для адресного API
     */
    public function getAddressCoords(string $fiasCode)
    {
        $this->client->authenticateClient();

        $respData = $this->client->api('/getAddress?fiasId='. $fiasCode);

        if (!isset($respData['suggestions']) || !is_array($suggestions = $respData['suggestions'])) {
            return null;
        }
        if (empty($suggestions) || 1 < count($suggestions)) {
            // Разводим руками, если вариантов нет или больше одного
            return null;
        }

        
        if (null === $coords = \Yii\helpers\ArrayHelper::getValue($suggestions, [0, 'data', 'polygon', 'coordinates'])) {
            // Нет координат
            return null;
        }
        
        // Ожидаемая структура объекта polygon.coordinates ([~]GeoJSON):
        // ```
        // coordinates: [
        //     [
        //         [
        //             [
        //                 37.5088118450342, // <-- долгота!
        //                 55.73737373966575 // <-- широта!
        //             ],
        //             // ...
        //         ]
        //     ]
        // ]
        // ```
        // Хотим вернуть первую точку

        if (!is_array($coords) || null === $point = \Yii\helpers\ArrayHelper::getValue($coords, [0, 0, 0])) {
            return null;
        }
        if (!is_array($point) || 2 !== count($point)) {
            return null;
        }

        return array_reverse($point);
    }

    /**
     * @param string $bound
     * @param string $query
     * @param string|null $locationType
     * @param string|null $locationFias
     * @return array|mixed
     */
    public function searchAllBound(string $bound, string $query, string $locationType = null, string $locationFias = null)
    {
        $this->client->authenticateClient();

        $response = $this->client->api('/searchAllBound', 'POST', json_encode([
            'count' => 25,
            'from_bound' => ['value' => $bound],
            'to_bound' => ['value' => $bound],
            'locations' => $locationType && $locationFias
                ? [ 0 => [$locationType => $locationFias] ]
                : null,
            'query' => $query,
            'restrict_value' => true,
        ]), ['content-type' => 'application/json']);

        // Небольшой костыль для фильтра по городу, чтобы получить опцию Москва
        // в связи с тем, что оригинальный api ищет по строке "Московская"
        if ($bound === 'city' && str_contains(mb_strtolower($query), 'москв') === true) {
            $response = $this->addMoscow($response);
        }
        return $response;
    }

    /**
     * @param string $locationFias
     * @param string $query
     * @return array
     */
    public function searchRoom(string $locationFias, string $query)
    {
        return $this->client->api('/searchRoom', 'POST', json_encode([
            'count' => 25,
            'house_fias_id' => $locationFias,
            'query' => $query
        ]), ['content-type' => 'application/json']);
    }

    /**
     * @param string $fiasId
     * @return array
     */
    public function getAddress(string $fiasId)
    {
        $this->client->authenticateClient();

        return $this->client->api('/getAddress?fiasId='. $fiasId);
    }

    /**
     * Тянет опцию в bound - city для опции Москва по захардкоженому guid опции
     *
     * @param $response
     * @return mixed
     */
    private function addMoscow($response)
    {
        $moscow = $this->getAddress(self::MOSCOW_CITY_FIAS_CODE);
        array_push($response['suggestions'], $moscow['suggestions'][0]);
        return $response;
    }
}
