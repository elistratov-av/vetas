<?php

namespace app\modules\v3\modules\analytics\controllers;

use Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use yii\caching\Cache;
use yii\validators\DateValidator;
use yii\web\Controller;

class ServicePriceController extends BaseController
{

    private static function getPriceServicesInfo($from, $to)
    {
        $ranges = Yii::$app->cache->get("servicePriceReportRanges");
        if ($ranges) {
            foreach ($ranges as $idx => $range) {
                if (strtotime($range->from) <= strtotime($from) && strtotime($range->to) >= strtotime($to)) {
                    if ($data = Yii::$app->cache->get("services-prices_{$from}_{$to}")) return $data;
                    else unset($ranges[$idx]);
                }
            }
        } else $ranges = [];
        $data = Yii::$app->db->createCommand("SELECT vgs.id_service, st.id AS type_id, st.name AS type_name, gs.name, v.id_organization AS organization_id, o.id_district AS district_id, ds.name as district_name, o.short_name as organization_name, COALESCE(a.name, 'Округ не указан') AS area_name, a.id AS area_id,
        vgs.price, vgs.count, vgs.price_with_discount, vgs.apply_discount, vgs.price, rab.total_rabies, v.is_blind, v.is_veteran, v.is_disabled, vgs.id_service AS service_id, vgs.id_service AS service_id, gs.name as service_name,
        v.is_orphan, v.is_large_family,
        vgs.price_with_discount * COALESCE(vgs.count, 1) AS total_amount,
        CASE WHEN (vgs.apply_discount = true AND vgs.price_with_discount = 0.00 AND vgs.price != vgs.price_with_discount) OR vgs.price = 0.00 THEN coalesce(vgs.count, 1) ELSE 0 END AS total_free,
        (COALESCE(vsd.total_f1, 0)) as total_f1,
        (COALESCE(vsd.total_f4, 0)) as total_f4,
        (COALESCE(vsd.total_ts, 0)) AS total_ts,
        COALESCE(v.fact_start_dttm::date, lower(v.time_range)::date) AS date
        FROM visits_gov_services vgs
        LEFT JOIN gov_services gs ON vgs.id_service = gs.id
        LEFT JOIN service_types st ON gs.id_service_type = st.id
        LEFT JOIN visits v ON vgs.id_visit = v.id
        LEFT JOIN organizations o ON v.id_organization = o.id
        LEFT JOIN visits_specialists vs ON vs.id_visit = v.id
        LEFT JOIN users u ON u.id = vs.id_specialist
        LEFT JOIN fias_addresses fa ON fa.id = o.id_fias_address
        LEFT JOIN areas a ON a.id = fa.id_area
        LEFT JOIN districts ds ON ds.id = fa.id_district
        LEFT JOIN (SELECT vgs.id, COUNT(vt.id) AS total_rabies
                    FROM  visits_gov_services vgs 
                    INNER JOIN visits v ON vgs.id_visit = v.id
                    INNER JOIN visit_pets vtp ON vtp.id_visit = v.id
                    LEFT JOIN visit_service_tmc_pet vsp ON vsp.id_pet = vtp.id_pet AND vsp.id_visits_gov_service = vgs.id
                    LEFT JOIN visit_service_tmc vt ON vsp.id_visit_service_tmc = vt.id AND vt.type_tmc = 'vaccine'
                    LEFT JOIN tmc.tmc_to_diseases ttd ON ttd.id_tmc = vt.id_tmc
                    LEFT JOIN tmc.tmc tmc ON tmc.id = vt.id_tmc
                    LEFT JOIN diseases d ON ttd.id_disease = d.id
                    WHERE d.name ilike '%бешенство%' AND tmc.name ilike '%рабикан%' GROUP BY  vgs.id) rab ON rab.id = vgs.id
        LEFT JOIN (SELECT vgs.id,
                    COUNT(DISTINCT CASE WHEN d.name = 'ветеринарная справка' THEN vgs.id END) as total_f4,
                    COUNT(DISTINCT CASE WHEN d.name = 'ветеринарный сертификат' THEN vgs.id END) as total_ts,
                    COUNT(DISTINCT CASE WHEN d.name = 'ветеринарное свидетельство' THEN vgs.id END) as total_f1
                    FROM visits_gov_services vgs
                    LEFT JOIN visit_service_param_values vspv ON vgs.id = vspv.id_visitservice
                    INNER JOIN dictionaries d ON vspv.dict_value = d.id
                    WHERE d.type = 'vsdtypes' GROUP BY vgs.id) vsd ON vsd.id = vgs.id
        LEFT JOIN visit_price vp ON v.id = vp.id_visit
        LEFT JOIN discount d ON d.id = vp.id_discount
        WHERE COALESCE(v.fact_start_dttm::date, lower(v.time_range)::date) BETWEEN '$from' AND '$to' AND v.status =  'F'")->queryAll();
        Yii::$app->cache->set("services-prices_{$from}_{$to}", $data, $to == date("Y-m-d") ? 3600 : 3600 * 24 * 7);
        $ranges[] = (object)[
            "from" => $from,
            "to" => $to
        ];
        Yii::$app->cache->set("servicePriceReportRanges", $ranges, 3600 * 24 * 7 * 30);
        return $data;
    }

    public function actionReport()
    {
        $request = Yii::$app->request;
        $from = trim($request->post('from'));
        $to = trim($request->post('to'));
        $areas = $request->post('areas') !== null ? json_decode($request->post('areas')) : null;
        $districts = $request->post('districts') !== null ? json_decode($request->post('districts')) : null;
        $organizations = $request->post('organizations') !== null ? json_decode($request->post('organizations')) : null;
        $validator = new DateValidator();
        $excel = $request->post('excel', false);
        $excel = !$excel || strtolower($excel) == 'false' || $excel == '0' ? false : true; 
        if (!$validator->validate($from) || !$validator->validate($to)) {
            return ['error' => 'Invalid date format'];
        }
        $data = self::getPriceServicesInfo($from, $to);
        $columns = [
            (object)['id' => 'price', 'name' => 'Тарифы с НДС', 'value' => 0],
            (object)['id' => 'count', 'name' => 'Количество платных ветеринарных услуг', 'value' => 0],
            (object)['id' => 'total_amount', 'name' => 'Общая стоимость платных услуг', 'value' => 0],
            (object)['id' => 'total_free', 'name' => 'Количество услуг со 100% скидкой', 'value' => 0],
            (object)['id' => 'is_blind', 'name' => 'Инвалиды по зрению', 'value' => 0],
            (object)['id' => 'is_veteran', 'name' => 'Ветераны ВОВ', 'value' => 0],
            (object)['id' => 'is_disabled', 'name' => 'Инвалиды 1 группы', 'value' => 0],
            (object)['id' => 'is_large_family', 'name' => 'Семьи, воспитывающие детей инвалидов в возрасте до 23 лет', 'value' => 0],
            (object)['id' => 'total_rabies', 'name' => 'Вакцинации Рабиканом', 'value' => 0],
            (object)['id' => 'total_f1', 'name' => 'Форма 1', 'value' => 0],
            (object)['id' => 'total_f4', 'name' => 'Форма 4', 'value' => 0],
            (object)['id' => 'total_ts', 'name' => 'Форма ТС', 'value' => 0],
        ];
        $res = (object)[
            'columns' => static::deepClone($columns),
            'areas' => []
        ];
        $allAreas = self::getAreas();
        $allDistricts = self::getDistricts();
        $allOrgs = self::getOrgs();
        foreach($allAreas as $area) {
            if ($areas && !in_array($area['id'], $areas)) continue;
            $res->areas[$area['id']] = (object)[
                'id' => $area['id'],
                'name' => $area['name'],
                'columns' => static::deepClone($columns),
                'districts' => []
            ];
        }
        foreach($allDistricts as $district) {
            if (!$district['area_id']) continue;
            if ($districts && !in_array($district['id'], $districts)) continue;
            $res->areas[$district['area_id']]->districts[$district['id']] = (object)[
                'id' => $district['id'],
                'name' => $district['name'],
                'columns' => static::deepClone($columns),
                'organizations' => []
            ];
        }
        foreach($allOrgs as $org) {
            if (!$org['area_id']) continue;
            if (!$org['district_id']) continue;
            if ($organizations && !in_array($org['id'], $organizations)) continue;
            $res->areas[$district['area_id']]->districts[$district['id']]->organizations[$org['id']] = (object)[
                'id' => $org['id'],
                'name' => $org['name'],
                'columns' => static::deepClone($columns),
                'services' => []
            ];
        }
        foreach ($data as $item) {
            if (!$item['organization_id']) continue;
            if (!$item['district_id']) continue;
            if (strtotime($item['date']) > strtotime($to) || strtotime($item['date']) < strtotime($from)) continue;
            if ($areas && !in_array($item['area_id'], $areas)) continue;
            if ($districts && !in_array($item['district_id'], $districts)) continue;
            if ($organizations && !in_array($item['organization_id'], $organizations)) continue;
            if (!isset($res->areas[$item['area_id']]))
                $res->areas[$item['area_id']] = (object)[
                    'id' => $item['area_id'],
                    'name' => $item['area_name'],
                    'columns' => static::deepClone($columns),
                    'districts' => []
                ];
            if (!isset($res->areas[$item['area_id']]->districts[$item['district_id']]))
                $res->areas[$item['area_id']]->districts[$item['district_id']] = (object)[
                    'id' => $item['district_id'],
                    'name' => $item['district_name'],
                    'columns' => static::deepClone($columns),
                    'organizations' => []
                ];
            if (!isset($res->areas[$item['area_id']]->districts[$item['district_id']]->organizations[$item['organization_id']]))
                $res->areas[$item['area_id']]->districts[$item['district_id']]->organizations[$item['organization_id']] = (object)[
                    'id' => $item['organization_id'],
                    'name' => $item['organization_name'],
                    'columns' => static::deepClone($columns),
                    'services' => []
                ];
            if (!isset($res->areas[$item['area_id']]->districts[$item['district_id']]->organizations[$item['organization_id']]->services[$item['service_id']]))
                $res->areas[$item['area_id']]->districts[$item['district_id']]->organizations[$item['organization_id']]->services[$item['service_id']] = (object)[
                    'id' => $item['service_id'],
                    'name' => $item['service_name'],
                    'columns' => static::deepClone($columns),
                ];
            foreach ($res->columns as $idx => $column) {
                if ($res->columns[$idx]->id != 'price') $res->columns[$idx]->value += $item[$res->columns[$idx]->id];
                else $res->columns[$idx]->value = $item[$res->columns[$idx]->id];
            }
            foreach ($res->areas[$item['area_id']]->columns as $idx => $column) {
                if ($res->areas[$item['area_id']]->columns[$idx]->id != 'price') $res->areas[$item['area_id']]->columns[$idx]->value += $item[$res->areas[$item['area_id']]->columns[$idx]->id];
                else $res->areas[$item['area_id']]->columns[$idx]->value = $item[$res->areas[$item['area_id']]->columns[$idx]->id];
            }
            foreach ($res->areas[$item['area_id']]->districts[$item['district_id']]->columns as $idx => $column) {
                if ($res->areas[$item['area_id']]->districts[$item['district_id']]->columns[$idx]->id != 'price') $res->areas[$item['area_id']]->districts[$item['district_id']]->columns[$idx]->value += $item[$res->areas[$item['area_id']]->districts[$item['district_id']]->columns[$idx]->id];
                else $res->areas[$item['area_id']]->districts[$item['district_id']]->columns[$idx]->value = $item[$res->areas[$item['area_id']]->districts[$item['district_id']]->columns[$idx]->id];
            }

            foreach ($res->areas[$item['area_id']]->districts[$item['district_id']]->organizations[$item['organization_id']]->columns as $idx => $column) {
                if ($res->areas[$item['area_id']]->districts[$item['district_id']]->organizations[$item['organization_id']]->columns[$idx]->id != 'price') $res->areas[$item['area_id']]->districts[$item['district_id']]->organizations[$item['organization_id']]->columns[$idx]->value += $item[$res->areas[$item['area_id']]->districts[$item['district_id']]->organizations[$item['organization_id']]->columns[$idx]->id];
                else $res->areas[$item['area_id']]->districts[$item['district_id']]->organizations[$item['organization_id']]->columns[$idx]->value = $item[$res->areas[$item['area_id']]->districts[$item['district_id']]->organizations[$item['organization_id']]->columns[$idx]->id];
            }
            foreach ($res->areas[$item['area_id']]->districts[$item['district_id']]->organizations[$item['organization_id']]->services[$item['service_id']]->columns as $idx => $column) {
                if ($res->areas[$item['area_id']]->districts[$item['district_id']]->organizations[$item['organization_id']]->services[$item['service_id']]->columns[$idx]->id != 'price') $res->areas[$item['area_id']]->districts[$item['district_id']]->organizations[$item['organization_id']]->services[$item['service_id']]->columns[$idx]->value += $item[$res->areas[$item['area_id']]->districts[$item['district_id']]->organizations[$item['organization_id']]->services[$item['service_id']]->columns[$idx]->id];
                else $res->areas[$item['area_id']]->districts[$item['district_id']]->organizations[$item['organization_id']]->services[$item['service_id']]->columns[$idx]->value = $item[$res->areas[$item['area_id']]->districts[$item['district_id']]->organizations[$item['organization_id']]->services[$item['service_id']]->columns[$idx]->id];
            }
        }
        $res->areas = array_values($res->areas);
        foreach ($res->areas as $idxArea => $area) {
            $res->areas[$idxArea]->districts = array_values($res->areas[$idxArea]->districts);
            foreach ($res->areas[$idxArea]->districts as $idxDs => $district) {
                $res->areas[$idxArea]->districts[$idxDs]->organizations = array_values($res->areas[$idxArea]->districts[$idxDs]->organizations);
                foreach ($res->areas[$idxArea]->districts[$idxDs]->organizations as $idxOrg => $organization) {
                    $res->areas[$idxArea]->districts[$idxDs]->organizations[$idxOrg]->services = array_values($res->areas[$idxArea]->districts[$idxDs]->organizations[$idxOrg]->services);
                }
            }
        }
        return $excel ? $this->generateExcelReport($res) : ['is_success' => true, 'data' => $res];
    }

    private function generateExcelReport($res)
    {
        static::setExcelHeaders();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getColumnDimensionByColumn(1)->setWidth(70);
        $sheet->getDefaultRowDimension()->setRowHeight(40);
        $columnCount = count($res->columns) + 1;
        $sheet->mergeCellsByColumnAndRow(1, 1, $columnCount, 1);
        $sheet->setCellValueByColumnAndRow(1, 1, "Отчёт об оказании ветеринарных услуг");
        $sheet->setCellValueByColumnAndRow(1, 2, "Название услуги");
        $sheet->mergeCellsByColumnAndRow(1, 2, 1, 4);
        $sheet->setCellValueByColumnAndRow(2, 2, $res->columns[0]->name);
        $sheet->mergeCellsByColumnAndRow(2, 2, 2, 4);
        $sheet->setCellValueByColumnAndRow(3, 2, $res->columns[1]->name);
        $sheet->mergeCellsByColumnAndRow(3, 2, 3, 4);
        $sheet->setCellValueByColumnAndRow(4, 2, $res->columns[2]->name);
        $sheet->mergeCellsByColumnAndRow(4, 2, 4, 4);
        $sheet->setCellValueByColumnAndRow(5, 2, $res->columns[3]->name);
        $sheet->mergeCellsByColumnAndRow(5, 2, 5, 4);
        $sheet->setCellValueByColumnAndRow(6, 2, "Количество ветеринарных услуг, оказанных в рамках госзадания");
        $sheet->mergeCellsByColumnAndRow(6, 2, $columnCount, 2);
        $sheet->setCellValueByColumnAndRow(6, 3, $res->columns[4]->name);
        $sheet->mergeCellsByColumnAndRow(6, 3, 6, 4);
        $sheet->setCellValueByColumnAndRow(7, 3, $res->columns[5]->name);
        $sheet->mergeCellsByColumnAndRow(7, 3, 7, 4);
        $sheet->setCellValueByColumnAndRow(8, 3, $res->columns[6]->name);
        $sheet->mergeCellsByColumnAndRow(8, 3, 8, 4);
        $sheet->setCellValueByColumnAndRow(9, 3, $res->columns[7]->name);
        $sheet->mergeCellsByColumnAndRow(9, 3, 9, 4);
        $sheet->setCellValueByColumnAndRow(10, 3, $res->columns[8]->name);
        $sheet->mergeCellsByColumnAndRow(10, 3, 10, 4);
        $sheet->setCellValueByColumnAndRow(11, 3, "Оформленные ВСД");
        $sheet->mergeCellsByColumnAndRow(11, 3, 13, 3);
        $sheet->setCellValueByColumnAndRow(11, 4, $res->columns[9]->name);
        $sheet->setCellValueByColumnAndRow(12, 4, $res->columns[10]->name);
        $sheet->setCellValueByColumnAndRow(13, 4, $res->columns[11]->name);
        $row = 5;
        $sheet->setCellValueByColumnAndRow(1, $row, "Итого");
        $sheet->mergeCellsByColumnAndRow(1, $row, 2, $row);
        foreach ($res->columns as $idx => $column) {
            if ($column->id == 'price') continue;
            $sheet->setCellValueByColumnAndRow(2 + $idx, $row, $column->value);
        }
        $row++;
        foreach ($res->areas as $area) {
            $sheet->setCellValueByColumnAndRow(1, $row, $area->name);
            $sheet->mergeCellsByColumnAndRow(1, $row, $columnCount, $row);
            $row++;
            foreach ($area->districts as $district) {
                $sheet->setCellValueByColumnAndRow(1, $row, $district->name);
                $sheet->mergeCellsByColumnAndRow(1, $row, $columnCount, $row);
                $row++;
                foreach ($district->organizations as $org) {
                    $sheet->setCellValueByColumnAndRow(1, $row, $org->name);
                    $sheet->mergeCellsByColumnAndRow(1, $row, $columnCount, $row);
                    $row++;
                    foreach ($org->services as $service) {
                        $sheet->setCellValueByColumnAndRow(1, $row, $service->name);
                        foreach ($service->columns as $idx => $column) {
                            $sheet->setCellValueByColumnAndRow(2 + $idx, $row, $column->value);
                        }
                        $row++;
                    }
                    $sheet->setCellValueByColumnAndRow(1, $row, "Итого по организации");
                    $sheet->mergeCellsByColumnAndRow(1, $row, 2, $row);
                    foreach ($org->columns as $idx => $column) {
                        if ($column->id == 'price') continue;
                        $sheet->setCellValueByColumnAndRow(2 + $idx, $row, $column->value);
                    }
                    $row++;
                }
                $sheet->setCellValueByColumnAndRow(1, $row, "Итого по району");
                $sheet->mergeCellsByColumnAndRow(1, $row, 2, $row);
                foreach ($district->columns as $idx => $column) {
                    if ($column->id == 'price') continue;
                    $sheet->setCellValueByColumnAndRow(2 + $idx, $row, $column->value);
                }
                $row++;
            }
            $sheet->setCellValueByColumnAndRow(1, $row, "Итого по округу");
            $sheet->mergeCellsByColumnAndRow(1, $row, 2, $row);
            foreach ($area->columns as $idx => $column) {
                if ($column->id == 'price') continue;
                $sheet->setCellValueByColumnAndRow(2 + $idx, $row, $column->value);
            }
            $row++;
        }
        $sheet->getStyleByColumnAndRow(1, 1, $columnCount, $row - 1)->applyFromArray(static::$CELL_STYLE);
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();
        Yii::$app->response->content = $content;
        return Yii::$app->response;
    }
}
