<?php

namespace app\modules\v3\modules\analytics\controllers;

use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Yii;
use yii\caching\Cache;
use app\modules\v3\modules\BaseController as Controller;

class BaseController extends Controller
{

    protected static $STATUSES = [
        ['id' => 'N', 'name' => 'Новый'],
        ['id' => 'F', 'name' => 'Завершен'],
        ['id' => 'A', 'name' => 'Отменен'],
        ['id' => 'W', 'name' => 'В работе'],
        ['id' => 'T', 'name' => 'Перенесен'],
        ['id' => 'C', 'name' => 'Изменен'],
        ['id' => 'D', 'name' => 'Пациент не явился'],
        ['id' => 'P', 'name' => 'Закрыто по тайм-ауту'],
        ['id' => 'O', 'name' => 'Завершен без оплаты'],
    ];

    protected static $VISIT_TYPES = [
        ['id' => 'VISIT', 'name' => 'Приём'],
        ['id' => 'CALL_TO_HOME', 'name' => 'Вызов на дом'],
        ['id' => 'AMBULANCE', 'name' => 'Вызов на дом (ВПД)'],
        ['id' => 'DETOUR', 'name' => 'Вакцинация на обходе'],
        ['id' => 'SHELTER', 'name' => 'Вакцинация в приюте']
    ];

    protected static $SPECIES = [
        ['id' => '9', 'name' => 'Кошки'],
        ['id' => '25', 'name' => 'Собаки'],
        ['id' => '0', 'name' => 'Другие'],
    ];

    protected static $CELL_STYLE = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['argb' => 'FF000000'],
            ],
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
            'wrapText' => true,
        ]
    ];

    protected static function deepClone($array)
    {
        $clonedArray = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $clonedArray[$key] = self::deepClone($value);
            } elseif (is_object($value)) {
                $clonedArray[$key] = clone $value;
                foreach ($value as $objKey => $objValue) {
                    if (is_array($objValue) || is_object($objValue)) {
                        $clonedArray[$key]->$objKey = self::deepClone($objValue);
                    } else {
                        $clonedArray[$key]->$objKey = $objValue;
                    }
                }
            } else {
                $clonedArray[$key] = $value;
            }
        }
        return $clonedArray;
    }

    protected static function getChannels() {
        $channels = Yii::$app->cache->get("channels");
        if (!$channels) {
            $preChannels = Yii::$app->db->createCommand("SELECT id, type, description as name FROM shift_type")->queryAll();
            foreach ($preChannels as $channel) {
                if (in_array($channel['type'], ['BREAK', 'SICK_LEAVE', 'VACATION', 'HOLIDAY', ''])) continue;
                $channel['count'] = 0;
                $channels[$channel['id']] = (object)$channel;
            }
            Yii::$app->cache->set("channels", $channels, 3600 * 24);
        }
        return $channels;
    }

    protected static function getAreas() {
        $areas = Yii::$app->cache->get("areas");
        if (!$areas) {
            $areas = Yii::$app->db->createCommand("SELECT id, name FROM areas")->queryAll();
            Yii::$app->cache->set("areas", $areas, 3600 * 24);
        }
        return $areas;
    }

    protected function getOrgs() {
        $organizations = Yii::$app->cache->get("organizations");
        if (!$organizations) {
            $organizations = Yii::$app->db->createCommand("SELECT id, short_name AS name, id_area AS area_id, id_district AS district_id FROM organizations WHERE organization_type_const != 'shelter' OR organization_type_const IS NULL")->queryAll();
            Yii::$app->cache->set("organizations", $organizations, 3600 * 24);
        }
        return $organizations;
    }

    protected function getDistricts() {
        $districts = Yii::$app->cache->get("districts");
        if (!$districts) {
            $districts = Yii::$app->db->createCommand("SELECT id, name, id_area AS area_id FROM districts")->queryAll();
            Yii::$app->cache->set("districts", $districts, 3600 * 24);
        }
        return $districts;
    }
    
    public function actionGetDictionary()
    {
        $areas = self::getAreas();
        $organizations = self::getOrgs();
        $specialists = Yii::$app->cache->get("specialists");
        $services = Yii::$app->cache->get("services");
        $channels = self::getChannels();
        $districts = self::getDistricts();
        if (!$specialists) {
            $specialists = Yii::$app->db->createCommand("SELECT specialists.id, users.fullname as name, specialists.id_organization as organization_id FROM specialists
            LEFT JOIN users ON specialists.id = users.id
            WHERE specialists.expel_date IS NOT NULL")->queryAll();
            Yii::$app->cache->set("specialists", $specialists, 3600 * 24);
        }
        if (!$services) {
            $servicesList = Yii::$app->db->createCommand("SELECT gov_services.id, gov_services.name, gov_services.id_service_type AS type_id, gov_services.cod AS code, service_types.name AS type_name FROM gov_services
            LEFT JOIN service_types ON service_types.id = gov_services.id_service_type
            WHERE gov_services.deleted = false
            ")->queryAll();
            $services = [];
            foreach ($servicesList as $service) {
                if (!isset($services[$service['type_id']])) $services[$service['type_id']] = (object)[
                    'id' => $service['type_id'],
                    'name' => $service['type_name'],
                    'services' => []
                ];
                $services[$service['type_id']]->services[] = (object)[
                    'id' => $service['id'],
                    'name' => $service['name'],
                    'code' => (int)$service['code'],
                ];
            }
            $services = array_values($services);
            foreach ($services as $idx => $service) {
                $services[$idx]->services = array_values($services[$idx]->services);
            }
            Yii::$app->cache->set("services", $services, 3600 * 24);
        }
        
        foreach ($channels as $idx => $channel) {
            unset($channels[$idx]->count);
            unset($channels[$idx]->type);
        }
        return [
            'is_success' => true,
            'data' => [
                'areas' => $areas,
                'organizations' => $organizations,
                'specialists' => $specialists,
                'services' => $services,
                'statuses' => static::$STATUSES,
                'types' => static::$VISIT_TYPES,
                'species' => static::$SPECIES,
                'channels' => array_values($channels),
                'districts' => $districts
            ]
        ];
    }

    protected function setExcelHeaders() {
        Yii::$app->response->headers->add('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        Yii::$app->response->headers->add('Content-Disposition', 'attachment;filename="Общий отчет по услугам.xlsx"');
        Yii::$app->response->headers->add('Cache-Control', 'max-age=0');
    }
}
