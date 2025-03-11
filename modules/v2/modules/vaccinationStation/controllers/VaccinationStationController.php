<?php

namespace app\modules\v2\modules\vaccinationStation\controllers;

use app\models\db\Specialists;
use app\models\db\VaccinationStation;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\vaccinationStation\models\VaccinationStationModel;
use DateTime;
use Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;
use yii\httpclient\Client;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;


/**
 * Class VaccinationStationController
 *
 * @package app\modules\v2\modules\vaccinationStation\controllers
 */
class VaccinationStationController extends BaseController
{
    public function actionListSpecialists(int $vaccination_station_id): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => VaccinationStationModel::getAllSpecialists($vaccination_station_id),
        ];
    }

    public function actionChangeSpecialists(
        int $vaccination_station_id,
        int $organization_id,
        array $specialists = [],
        ?int $old_organization_id = 0
    ): array {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $vaccinationStation = VaccinationStation::findOne($vaccination_station_id);

        $specialist2VaccinationStation = $vaccinationStation->specialist2VaccinationStation;
        foreach ($specialist2VaccinationStation as $item) {
            if (in_array($item->organization_id, [$organization_id, $old_organization_id])) {
                $item->delete();
            }
        }

        foreach ($specialists as $specialistId) {
            $specialist = Specialists::findOne($specialistId);
            $vaccinationStation->link('specialists', $specialist, [
                'organization_id' => $organization_id,
            ]);
        }

        return [
            'result' => true,
        ];
    }

    /**
     * @param int $id
     *
     * @return array
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     */
    public function actionGet(int $id): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => $this->findModel($id),
        ];
    }

    /**
     * @param int   $page
     * @param int   $limit
     * @param array $filter
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws ForbiddenHttpException
     */
    public function actionList(int $page = 1, int $limit = 10, array $filter = []): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        return [
            'result' => VaccinationStationModel::getAll($page, $limit, $filter),
        ];
    }

    /**
     * @param int         $parent_id
     * @param string      $number
     * @param int         $kind_vc_id
     * @param string      $date
     * @param string      $time_from
     * @param string      $time_to
     * @param string      $area_id
     * @param string      $district_id
     * @param array       $fias_address
     * @param int|null    $reason_vc_id
     * @param string|null $name
     * @param string|null $short_name
     * @param string|null $address_comment
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function actionCreate(
        int $parent_id,
        string $number,
        int $kind_vc_id,
        string $date,
        string $time_from,
        string $time_to,
        string $area_id,
        string $district_id,
        array $fias_address,
        int $reason_vc_id = null,
        string $name = null,
        string $short_name = null,
        string $address_comment = null
    ): array {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $vaccinationStation = VaccinationStationModel::saveOrUpdate(
            $parent_id,
            $number,
            $kind_vc_id,
            $date,
            $time_from,
            $time_to,
            $area_id,
            $district_id,
            $fias_address,
            $reason_vc_id,
            $name,
            $short_name,
            $address_comment
        );

        return [
            'result' => true,
            'id' => $vaccinationStation->id,
        ];
    }

    /**
     * @return array
     */
    public function actionCreateList(): array
    {
        $db = Yii::$app->db;
        $file = UploadedFile::getInstanceByName('file');
        $tempFilePath = Yii::getAlias('@webroot/upload/') . uniqid() . '.' . $file->extension;
        if (!$file->saveAs($tempFilePath)) {
            throw new BadRequestHttpException('Failed to save file. Error: ' . print_r($file->error, true));
        }
        $spreadsheet = IOFactory::load($tempFilePath);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        unlink($tempFilePath);
        if (in_array(trim(mb_strtolower($sheetData[1]['A'])), ['№ п/п', '№ п\п'])) unset($sheetData[1]);
        $sheetData = array_values($sheetData);

        $parentOrgs = [];
        $areas = [];
        $districts = [];
        $types = [];
        $queryData = [];
        $result = [];
        $specialists = [];


        foreach ($sheetData as &$row) {
            foreach ($row as &$item) $item = trim($item);
            $areas[] = $row['B'];
            $districts[] = $row['C'];
            $types[] = $row['G'];
            $parentOrgs[] = $row['I'];
            $specialistsList = explode(',', $row['H']);
            $specialistsList = array_map(function ($item) {
                return trim($item);
            }, $specialistsList);
            $specialists = array_merge($specialists, $specialistsList);
        }

        $districts = implode(',', array_map(function ($item) {
            return "'%$item%'";
        }, $districts));
        $areas = implode(',', array_map(function ($item) {
            return "'%$item%'";
        }, $areas));
        $types = implode(',', array_map(function ($item) {
            return "'%$item%'";
        }, $types));
        $parentOrgs = implode(',', array_map(function ($item) {
            return "'%$item%'";
        }, $parentOrgs));
        $specialists = implode(',', array_map(function ($item) {
            return "'%$item%'";
        }, $specialists));
        $areas = $db->createCommand("SELECT id, name FROM areas WHERE  name ILIKE ANY (ARRAY[$areas])")->queryAll();
        $districts = $db->createCommand("SELECT id, name FROM districts WHERE  name ILIKE ANY (ARRAY[$districts])")->queryAll();
        $types = $db->createCommand("SELECT id, name FROM kind_vc WHERE  name ILIKE ANY (ARRAY[$types])")->queryAll();
        $parentOrgs = $db->createCommand("SELECT id, CONCAT(name, ' (', short_name, ')') as name FROM organizations WHERE name ILIKE ANY (ARRAY[$parentOrgs]) OR short_name ILIKE ANY (ARRAY[$parentOrgs])")->queryAll();
        $specialists = mb_strlen($specialists) ? $db->createCommand("SELECT id, CONCAT(f_fio, ' ', i_fio, ' ', o_fio, ' (', fullname, ')') as name FROM users
        WHERE
        f_fio ILIKE ANY (ARRAY[$specialists])
            OR
        i_fio ILIKE ANY (ARRAY[$specialists])
            OR
        o_fio ILIKE ANY (ARRAY[$specialists])
            OR
        fullname ILIKE ANY (ARRAY[$specialists])
        ")->queryAll() : [];
        foreach ($sheetData as $idx => &$row) {
            if (!mb_strlen($row['A'])) continue;
            $status = 'failed';
            $vaccinationStation = null;
            $errors = [];
            $parent = self::defineByName($parentOrgs, $row['I']);
            if (!$parent) $errors['parent_id'] = true;

            $kindVc = self::defineByName($types, $row['G']);
            if (!$kindVc) $errors['kind_vc_id'] = true;

            $date = null;
            try {
                $date = (new DateTime($row['E']))->format('Y-m-d');
            } catch (Exception $e) {
                $errors['date'] = true;
            }
            $timeParts = explode('-', $row['F']);
            $timeParts = array_pad($timeParts, 2, null);
            list($from, $to) = $timeParts;
            if (!preg_match('/^\d{2}:\d{2}$/', $from)) $errors[] = $errors['time_from'] = true;
            if (!preg_match('/^\d{2}:\d{2}$/', $to)) $errors[] = $errors['time_to'] = true;

            $area = self::defineByName($areas, $row['B']);
            if (!$area) $errors['area_id'] = true;

            $district = self::defineByName($districts, $row['C']);
            if (!$district) $errors['district_id'] = true;
            if (!self::getArea($area, $district)) {
                $errors['area_id'] = true;
                $errors['district_id'] = true;
            }

            if (!sizeof($errors)) {
                $status = 'warning';
                $fias = self::getFias($row['D']);
                $streetName = $fias['street'] ? $fias['street'] : trim(explode(',', $row['D'])[0] ?? '');
                $nameDate = date('d.m.Y', strtotime($date));
                $vaccinationStation = VaccinationStationModel::saveOrUpdate(
                    $parent['id'],
                    $row['A'],
                    $kindVc['id'],
                    $date,
                    $from,
                    $to,
                    $district['code'],
                    $area['code'],
                    $fias,
                    null,
                    "{$row['A']} {$area['name']} {$district['name']} {$streetName} $nameDate",
                    "{$row['A']} {$district['name']} {$streetName} $nameDate",
                    "",
                    null,
                    true
                );
                if ($vaccinationStation && mb_strlen($row['I'])) {
                    $specialist = self::defineByName($specialists, trim($row['H']));
                    if ($specialist) {
                        $specialist = Specialists::findOne(['id_user' => (int)$specialist['id']]);
                        $vaccinationStation->link('specialists', $specialist, [
                            'organization_id' => $parent['id'],
                        ]);
                        $status = 'success';
                    } else $errors['specialist'] = true;
                } else $status = 'success';
            }

            $result[] = [
                'errors' => (object)$errors,
                'station' => $vaccinationStation,
                'status' => $status
            ];
        }

        return [
            'is_success' => true,
            'data' => $result
        ];
    }

    private static function defineByName($array, $txt)
    {
        $filtered = array_filter($array, function ($el) use ($txt) {
            return stripos(mb_strtolower($el['name']), mb_strtolower($txt)) !== false;
        });
        $filtered = array_values($filtered);
        return $filtered[0] ?? null;
    }

    private static function getArea(&$inputArea, &$inputDistrict)
    {
        try {
            $inputArea['code'] = null;
            $inputDistrict['code'] = null;
            $client = new Client();
            $token = $client->createRequest()
                ->setMethod('POST')
                ->setUrl('https://api.mos.ru/token')
                ->setHeaders(['Authorization' => 'Basic Q3NTUnFBb21Wb1p6MmZTY203ajdyV0EwMmNFYTpvR0tPZk1kZHR4dnFVNjRrVGYwNnJRbmNHSWdh'])
                ->setData(['grant_type' => 'client_credentials'])
                ->send();
            $token = $token->isOk ? $token->data : null;
            if (!$token) return null;
            $token = $token['access_token'];
            $response = $client->createRequest()
                ->setUrl("{$_ENV['FIAS_URL']}/districts")
                ->setHeaders(['Authorization' => "Bearer $token"])
                ->setData([
                    "fias_id" => '0c5b2444-70a0-4932-980c-b4dc0d3f02b5'
                ])
                ->send();
            foreach ($response->data['level2'] as $area) {
                if (mb_strtolower($inputArea['name']) == mb_strtolower($area['name'] . " " . $area['type'])) {
                    $inputArea['code'] = $area['code'];
                    foreach ($area['level3'] as $district) {
                        if (mb_strtolower($inputDistrict['name']) == mb_strtolower($district['name'])) {
                            $inputDistrict['code'] = $district['code'];
                            return true;
                        }
                    }
                }
            }
        } catch(Exception $e) {
            return false;
        }
        return false;
    }

    private static function getFias($streetName)
    {
        $fias = [
            'region' => 'Город Москва',
            "regionguid" => 77,
            'city' => 'Город Москва',
            'cityguid' => '0c5b2444-70a0-4932-980c-b4dc0d3f02b5',
            'street' => null,
            'streetguid' => null,
            'house' => null,
            'houseguid' => null,
            'room' => null,
            'roomguid' => null
        ];
        $client = new Client();
        $parts = explode(',', $streetName);
        $parts = array_pad($parts, 3, null);
        list($street, $house, $room) = $parts;
        $street = $street ? trim($street) : null;
        $house = $house ? trim($house) : null;
        $room = $room ? trim($room) : null;
        $bearer = null;
        if ($street) {
            $token = $client->createRequest()
                ->setMethod('POST')
                ->setUrl('https://api.mos.ru/token')
                ->setHeaders(['Authorization' => 'Basic Q3NTUnFBb21Wb1p6MmZTY203ajdyV0EwMmNFYTpvR0tPZk1kZHR4dnFVNjRrVGYwNnJRbmNHSWdh'])
                ->setData(['grant_type' => 'client_credentials'])
                ->send();
            $token = $token->isOk ? $token->data : null;
            if (!$token) return $fias;
            $token = $token['access_token'];
            $response = null;
            $response = $client->createRequest()
                ->setMethod('POST')
                ->setUrl("{$_ENV['FIAS_URL']}/searchAllBound")
                ->setFormat(Client::FORMAT_JSON)
                ->setHeaders(['Authorization' => "Bearer $token"])
                ->setData([
                    "count" => 1,
                    "from_bound" => [
                        "value" => "street"
                    ],
                    "locations" => [
                        [
                            "region_fias_id" => "0c5b2444-70a0-4932-980c-b4dc0d3f02b5"
                        ]
                    ],
                    "query" => $street,
                    "restrict_value" => true,
                    "to_bound" => [
                        "value" => "street"
                    ]
                ])
                ->send();
            if (isset($response->data['suggestions'][0])) {
                $fias['street'] = $response->data['suggestions'][0]['value'];
                $fias['streetguid'] = $response->data['suggestions'][0]['data']['street_fias_id'];
            } else return $fias;
        } else return $fias;
        if ($house) {
            $response = $client->createRequest()
                ->setMethod('POST')
                ->setUrl("{$_ENV['FIAS_URL']}/searchAllBound")
                ->setFormat(Client::FORMAT_JSON)
                ->setHeaders(['Authorization' => "Bearer $token"])
                ->setData([
                    "count" => 1,
                    "from_bound" => [
                        "value" => "house"
                    ],
                    "locations" => [
                        [
                            "street_fias_id" => $fias['streetguid']
                        ]
                    ],
                    "query" => $house,
                    "restrict_value" => true,
                    "to_bound" => [
                        "value" => "street"
                    ]
                ])
                ->send();
            if (isset($response->data['suggestions'][0])) {
                $fias['house'] = "{$response->data['suggestions'][0]['data']['house_type_full']} {$response->data['suggestions'][0]['data']['house_numb']} {$response->data['suggestions'][0]['data']['block_type_full']} {$response->data['suggestions'][0]['data']['block']}";
                $fias['houseguid'] = $response->data['suggestions'][0]['data']['house_fias_id'];
            } else return $fias;
        } else return $fias;
        if ($room) {
            $response = $client->createRequest()
                ->setMethod('POST')
                ->setUrl("{$_ENV['FIAS_URL']}/searchRoom")
                ->setFormat(Client::FORMAT_JSON)
                ->setHeaders(['Authorization' => "Bearer $token"])
                ->setData([
                    "count" => 1,
                    "house_fias_id" => $fias['houseguid'],
                    "query" => $room,
                ])
                ->send();
            if (isset($response->data['suggestions'][0])) {
                $fias['room'] = "{$response->data['suggestions'][0]['data']['flat_type_full']} {$response->data['suggestions'][0]['data']['flat']}";
                $fias['roomguid'] = $response->data['suggestions'][0]['data']['room_fias_id'];
            }
        }
        return $fias;
    }


    /**
     * @param int         $id
     * @param int         $parent_id
     * @param string      $number
     * @param int         $kind_vc_id
     * @param string      $date
     * @param string      $time_from
     * @param string      $time_to
     * @param string      $area_id
     * @param string      $district_id
     * @param array       $fias_address
     * @param int|null    $reason_vc_id
     * @param string|null $name
     * @param string|null $short_name
     * @param string|null $address_comment
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws StaleObjectException
     * @throws Throwable
     */
    public function actionEdit(
        int $id,
        int $parent_id,
        string $number,
        int $kind_vc_id,
        string $date,
        string $time_from,
        string $time_to,
        string $area_id,
        string $district_id,
        array $fias_address,
        int $reason_vc_id = null,
        string $name = null,
        string $short_name = null,
        string $address_comment = null
    ): array {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        $vaccinationStation = VaccinationStationModel::saveOrUpdate(
            $parent_id,
            $number,
            $kind_vc_id,
            $date,
            $time_from,
            $time_to,
            $area_id,
            $district_id,
            $fias_address,
            $reason_vc_id,
            $name,
            $short_name,
            $address_comment,
            $id
        );

        return [
            'result' => true,
            'id' => $vaccinationStation->id,
        ];
    }

    /**
     * @param int $id
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws Throwable
     * @throws StaleObjectException
     * @throws ForbiddenHttpException
     */
    public function actionDelete(int $id): array
    {
        $this->checkAccess($this->action->getUniqueId(), null, $this->actionParams);

        VaccinationStationModel::delete($id);

        return [
            'result' => true,
        ];
    }

    /**
     * @param int $id
     *
     * @return array
     * @throws NotFoundHttpException
     */
    protected function findModel(int $id): array
    {
        if (($model = VaccinationStationModel::get($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Vaccination station doesn\'t exists.');
    }
}
