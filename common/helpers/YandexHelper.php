<?php

namespace app\common\helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\RequestOptions;

class YandexHelper
{
    protected static $timeOut = 60;

    public static function geocode($addr)
    {
        try {
            $client = new Client();
            $response = $client->get('https://geocode-maps.yandex.ru/1.x/?format=json&sensor=false&region=ru&ll=37.3638358867367,55.559228578773&spn=3,1.3&geocode=' . $addr, [RequestOptions::TIMEOUT => self::$timeOut, RequestOptions::CONNECT_TIMEOUT => self::$timeOut]);

            $yandexData = $response->getBody()->getContents();
            $yandex = json_decode($yandexData, true);

            if (isset($yandex['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['Point']['pos'])) {
                $geoString = $yandex['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['Point']['pos'];
                $geoArr = explode(" ", $geoString);
                return ['latitude' => $geoArr[1], 'longitude' => $geoArr[0]];
            } else {
                return [];
            }
        } catch (ClientException $e) {
            throw new \Exception($e->getResponse()->getBody());
        } catch (ServerException $e) {
            throw new \Exception("Внешний сервис не отвечает", 422);
        } catch (ConnectException $e) {
            throw new \Exception("Превышено время ожидания ответа от внешнего сервиса", 422);
        } catch (BadResponseException $e) {
            throw new \Exception($e->getResponse()->getBody(), 400);
        }
    }

    public static function fullGeocode($addr)
    {
        $client = new Client();
        $response = $client->get(getenv('URL_YANDEX') . $addr, [RequestOptions::TIMEOUT => self::$timeOut, RequestOptions::CONNECT_TIMEOUT => self::$timeOut]);
        $yandexData = $response->getBody()->getContents();
        $yandex = json_decode($yandexData, true);
        if (isset($yandex['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['Point']['pos'])) {
            $geoString = $yandex['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['Point']['pos'];
            $geoArr = explode(" ", $geoString);
            return [
                'latitude' => $geoArr[1],
                'longitude' => $geoArr[0],
                'address' => $yandex['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['name'],
                'json' => $yandexData,
            ];
        } else {
            return [];
        }
    }

    /**
     * В случае ошибки парсинг не падает, а выдает массив, где координаты null
     * @param $addr
     * @return array
     */
    public static function geocodeErrorMute($addr)
    {
        try {
            return self::geocode($addr);
        } catch (\Exception $e) {
            return [
                'latitude' => null,
                'longitude' => null
            ];
        }
    }
}
