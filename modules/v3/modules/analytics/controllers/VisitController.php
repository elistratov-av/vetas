<?php

namespace app\modules\v3\modules\analytics\controllers;

use Yii;
use yii\validators\DateValidator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class VisitController extends BaseController
{

    private static function getVisitsInfo($from, $to)
    {
        $ranges = Yii::$app->cache->get("visitReportRanges");
        if ($ranges) {
            foreach ($ranges as $idx => $range) {
                if (strtotime($range->from) <= strtotime($from) && strtotime($range->to) >= strtotime($to)) {
                    if ($data = Yii::$app->cache->get("visits_{$from}_{$to}")) return $data;
                    else unset($ranges[$idx]);
                }
            }
        } else $ranges = [];
        $data = Yii::$app->db->createCommand("WITH pet_info AS (
        SELECT 
            vp.id_visit,
            MAX(CASE WHEN pets.id_species = 9 THEN 1 ELSE 0 END) = 1 AS has_cats,
            MAX(CASE WHEN pets.id_species = 25 THEN 1 ELSE 0 END) = 1 AS has_dogs,
            MAX(CASE WHEN pets.id_species NOT IN (9, 25) THEN 1 ELSE 0 END) = 1 AS has_other,
            pets.id_species
        FROM visit_pets vp
        LEFT JOIN pets ON pets.id = vp.id_pet
        JOIN visits v ON vp.id_visit = v.id
        WHERE COALESCE(v.fact_start_dttm, v.start_dttm) :: DATE BETWEEN '$from' AND '$to'
        GROUP BY vp.id_visit, pets.id_species
        ),
	    status_info AS (
            SELECT DISTINCT ON (visit_id)
                visit_id,
                log_time,
                etp_status
            FROM etp.status_log
            JOIN visits v ON etp.status_log.visit_id = v.id
            WHERE COALESCE(v.fact_start_dttm, v.start_dttm) :: DATE BETWEEN '$from' AND '$to'
            ORDER BY visit_id, log_time DESC
        )
        SELECT 
            o.id AS org_id, 
            o.short_name AS org_name, 
            o.id_fias_address, 
            fa.id_area AS area_id, 
            a.name AS area_name,
            fa.id_district AS district_id, 
            d.name AS district_name, 
            v.channel, 
            v.status, 
            v.cancel_initiator,
            pet_info.has_cats,
            pet_info.has_dogs,
            pet_info.has_other,
            pet_info.id_species as species_id,
            status_info.etp_status
        FROM organizations o
        LEFT JOIN fias_addresses fa ON fa.id = o.id_fias_address
        LEFT JOIN areas a ON a.id = fa.id_area
        LEFT JOIN districts d ON d.id = fa.id_district
        LEFT JOIN visits v ON o.id = v.id_organization
        LEFT JOIN pet_info ON pet_info.id_visit = v.id
        LEFT JOIN status_info ON status_info.visit_id = v.id
        WHERE COALESCE(v.fact_start_dttm, v.start_dttm) :: DATE BETWEEN '$from' AND '$to'")->queryAll();
        Yii::$app->cache->set("visits_{$from}_{$to}", $data, $to == date("Y-m-d") ? 3600 : 3600 * 24 * 7);
        $ranges[] = (object)[
            "from" => $from,
            "to" => $to
        ];
        Yii::$app->cache->set("visitReportRanges", $ranges, 3600 * 24 * 7 * 30);
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

        $data = self::getVisitsInfo($from, $to);
        $channels = self::getChannels();
        $columns = [
            'total' => [
                'name' => 'Всего',
                'count' => 0,
                'id' => 'total'
            ],
            'A' => [
                'name' => 'Отменено',
                'count' => 0,
                'id' => 'A'
            ],
            'T' => [
                'name' => 'Перенесено',
                'count' => 0,
                'id' => 'T'
            ],
            'F' => [
                'name' => 'Завершено',
                'count' => 0,
                'id' => 'F'
            ]
        ];
        $res = [
            'count' => self::deepClone($columns),
            'areas' => []
        ];
        $allAreas = self::getAreas();
        $allDistricts = self::getDistricts();
        $allOrgs = self::getOrgs();
        foreach($allAreas as $area) {
            if ($areas && !in_array($area['id'], $areas)) continue;
            $res['areas'][$area['id']] = [
                'id' => $area['id'],
                'name' => $area['name'],
                'count' => static::deepClone($columns),
                'districts' => []
            ];
        }
        foreach($allDistricts as $district) {
            if (!$district['area_id']) continue;
            if ($districts && !in_array($district['id'], $districts)) continue;
            $res['areas'][$district['area_id']]['districts'][$district['id']] = [
                'id' => $district['id'],
                'name' => $district['name'],
                'count' => static::deepClone($columns),
                'organizations' => []
            ];
        }
        foreach($allOrgs as $org) {
            if (!$org['area_id']) continue;
            if (!$org['district_id']) continue;
            if ($organizations && !in_array($org['id'], $organizations)) continue;
            $res['areas'][$district['area_id']]['districts'][$district['id']]['organizations'][$org['id']] = [
                'id' => $org['id'],
                'name' => $org['name'],
                'count' => static::deepClone($columns),
                'channels' => []
            ];
        }
        foreach ($data as $item) {
            if (!$item['org_id']) continue;
            if (!$item['district_id']) continue;
            if ($areas && !in_array($item['area_id'], $areas)) continue;
            if ($districts && !in_array($item['district_id'], $districts)) continue;
            if ($organizations && !in_array($item['org_id'], $organizations)) continue;
            if (!isset($res['areas'][$item['area_id']])) $res['areas'][$item['area_id']] = [
                'id' => $item['area_id'],
                'name' => $item['area_name'],
                'count' => self::deepClone($columns),
                'districts' => []
            ];
            if (!isset($res['areas'][$item['area_id']]['districts'][$item['district_id']])) $res['areas'][$item['area_id']]['districts'][$item['district_id']] = [
                'id' => $item['district_id'],
                'name' => $item['district_name'],
                'count' => self::deepClone($columns),
                'organizations' => []
            ];
            if (!isset($res['areas'][$item['area_id']]['districts'][$item['district_id']]['organizations'][$item['org_id']])) {
                $res['areas'][$item['area_id']]['districts'][$item['district_id']]['organizations'][$item['org_id']] = [
                    'id' => $item['org_id'],
                    'name' => $item['org_name'],
                    'count' => self::deepClone($columns),
                    'channels' => self::deepClone($channels)
                ];
                foreach ($res['areas'][$item['area_id']]['districts'][$item['district_id']]['organizations'][$item['org_id']]['channels'] as $idx => $channel) {
                    $res['areas'][$item['area_id']]['districts'][$item['district_id']]['organizations'][$item['org_id']]['channels'][$idx] = [
                        'id' => $channel->id,
                        'name' => $channel->name,
                        'count' => self::deepClone($columns),
                    ];
                }
            }
            $inCol = in_array($item['status'], ['T', 'A', 'F']);
            $res['count']['total']['count']++;
            if ($inCol) $res['count'][$item['status']]['count']++;
            $res['areas'][$item['area_id']]['count']['total']['count']++;
            if ($inCol) $res['areas'][$item['area_id']]['count'][$item['status']]['count']++;
            $res['areas'][$item['area_id']]['districts'][$item['district_id']]['count']['total']['count']++;
            if ($inCol) $res['areas'][$item['area_id']]['districts'][$item['district_id']]['count'][$item['status']]['count']++;
            $res['areas'][$item['area_id']]['districts'][$item['district_id']]['organizations'][$item['org_id']]['count']['total']['count']++;
            if ($inCol) $res['areas'][$item['area_id']]['districts'][$item['district_id']]['organizations'][$item['org_id']]['count'][$item['status']]['count']++;
            $res['areas'][$item['area_id']]['districts'][$item['district_id']]['organizations'][$item['org_id']]['channels'][$item['channel']]['count']['total']['count']++;
            if ($inCol) $res['areas'][$item['area_id']]['districts'][$item['district_id']]['organizations'][$item['org_id']]['channels'][$item['channel']]['count'][$item['status']]['count']++;
        }
        $res['count'] = array_values($res['count']);
        $res['areas'] = array_values($res['areas']);
        foreach ($res['areas'] as $idx => $area) {
            $res['areas'][$idx]['count'] = array_values($area['count']);
            $res['areas'][$idx]['districts'] = array_values($area['districts']);
            foreach ($res['areas'][$idx]['districts'] as $dIdx => $district) {
                $res['areas'][$idx]['districts'][$dIdx]['count'] = array_values($district['count']);
                $res['areas'][$idx]['districts'][$dIdx]['organizations'] = array_values($district['organizations']);
                foreach ($res['areas'][$idx]['districts'][$dIdx]['organizations'] as $oIdx => $org) {
                    $res['areas'][$idx]['districts'][$dIdx]['organizations'][$oIdx]['count'] = array_values($org['count']);
                    $res['areas'][$idx]['districts'][$dIdx]['organizations'][$oIdx]['channels'] = array_values($org['channels']);
                    foreach ($res['areas'][$idx]['districts'][$dIdx]['organizations'][$oIdx]['channels'] as $chIdx => $channel) {
                        $res['areas'][$idx]['districts'][$dIdx]['organizations'][$oIdx]['channels'][$chIdx]['count'] = array_values($channel['count']);
                    }
                }
            }
        }
        return $excel ? $this->generateExcelReport($res) : ['is_success' => true, 'data' => $res];
    }

    public function actionDetailReport()
    {
        $request = Yii::$app->request;
        $from = trim($request->post('from'));
        $to = trim($request->post('to'));
        $areas = $request->post('areas') !== null ? json_decode($request->post('areas')) : null;
        $districts = $request->post('districts') !== null ? json_decode($request->post('districts')) : null;
        $organizations = $request->post('organizations') !== null ? json_decode($request->post('organizations')) : null;
        $species = $request->post('species') !== null ? json_decode($request->post('species')) : null;
        $validator = new DateValidator();
        $excel = $request->post('excel', false);
        $excel = !$excel || strtolower($excel) == 'false' || $excel == '0' ? false : true;
        if (!$validator->validate($from) || !$validator->validate($to)) {
            return ['error' => 'Invalid date format'];
        }

        $data = self::getVisitsInfo($from, $to);
        $columns = [
            'total' => [
                'name' => 'Всего',
                'count' => 0,
                'id' => 'total'
            ],
            'F' => [
                'name' => 'Завершено',
                'count' => 0,
                'id' => 'F'
            ],
            'AOR' => [
                'name' => 'Отменено организацией',
                'count' => 0,
                'id' => 'AOR'
            ],
            'AOW' => [
                'name' => 'Отменено владельцем',
                'count' => 0,
                'id' => 'AOW'
            ],
            'T' => [
                'name' => 'Перенесено',
                'count' => 0,
                'id' => 'T'
            ],
            'TOR' => [
                'name' => 'Перенесено организацией (mos.ru)',
                'count' => 0,
                'id' => 'TOR'
            ],
            'TOW' => [
                'name' => 'Перенесено владельцем (mos.ru)',
                'count' => 0,
                'id' => 'TOW'
            ]
        ];

        $speciesCols = [];

        foreach (self::$SPECIES as $spec) {
            $speciesCols[$spec['id']] = [
                'id' => $spec['id'],
                'name' => $spec['name'],
                'count' => self::deepClone($columns)
            ];
        }

        $res = [
            'count' => self::deepClone($columns),
            'areas' => []
        ];
        $allAreas = self::getAreas();
        $allDistricts = self::getDistricts();
        $allOrgs = self::getOrgs();
        foreach($allAreas as $area) {
            if ($areas && !in_array($area['id'], $areas)) continue;
            $res['areas'][$area['id']] = [
                'id' => $area['id'],
                'name' => $area['name'],
                'count' => static::deepClone($columns),
                'districts' => []
            ];
        }
        foreach($allDistricts as $district) {
            if (!$district['area_id']) continue;
            if ($districts && !in_array($district['id'], $districts)) continue;
            $res['areas'][$district['area_id']]['districts'][$district['id']] = [
                'id' => $district['id'],
                'name' => $district['name'],
                'count' => static::deepClone($columns),
                'organizations' => []
            ];
        }
        foreach($allOrgs as $org) {
            if (!$org['area_id']) continue;
            if (!$org['district_id']) continue;
            if ($organizations && !in_array($org['id'], $organizations)) continue;
            $res['areas'][$district['area_id']]['districts'][$district['id']]['organizations'][$org['id']] = [
                'id' => $org['id'],
                'name' => $org['name'],
                'count' => static::deepClone($columns),
                'species' => static::deepClone($speciesCols)
            ];
        }
        foreach ($data as $item) {
            if (!$item['org_id']) continue;
            if (!$item['district_id']) continue;
            if ($areas && !in_array($item['area_id'], $areas)) continue;
            if ($districts && !in_array($item['district_id'], $districts)) continue;
            if ($organizations && !in_array($item['org_id'], $organizations)) continue;
            if ($species) {
                if (!in_array(0, $species) && !in_array(9, $species) && !in_array(25, $species)) continue;
                if (in_array(0, $species) && !in_array(9, $species) && !in_array(25, $species)) {
                    if (!$item['has_others']) continue;
                }
                if (in_array(0, $species) && in_array(9, $species) && !in_array(25, $species)) {
                    if (!$item['has_others'] && !$item['has_cats']) continue;
                }
                if (in_array(0, $species) && !in_array(9, $species) && in_array(25, $species)) {
                    if (!$item['has_others'] && !$item['has_dogs']) continue;
                }
                if (!in_array(0, $species) && in_array(9, $species) && !in_array(25, $species)) {
                    if (!$item['has_cats']) continue;
                }
                if (!in_array(0, $species) && in_array(9, $species) && in_array(25, $species)) {
                    if (!$item['has_cats'] && !$item['has_dogs']) continue;
                }
            }
            if (!isset($res['areas'][$item['area_id']])) $res['areas'][$item['area_id']] = [
                'id' => $item['area_id'],
                'name' => $item['area_name'],
                'count' => self::deepClone($columns),
                'districts' => []
            ];
            if (!isset($res['areas'][$item['area_id']]['districts'][$item['district_id']])) $res['areas'][$item['area_id']]['districts'][$item['district_id']] = [
                'id' => $item['district_id'],
                'name' => $item['district_name'],
                'count' => self::deepClone($columns),
                'organizations' => []
            ];
            if (!isset($res['areas'][$item['area_id']]['districts'][$item['district_id']]['organizations'][$item['org_id']]))
                $res['areas'][$item['area_id']]['districts'][$item['district_id']]['organizations'][$item['org_id']] = [
                    'id' => $item['org_id'],
                    'name' => $item['org_name'],
                    'count' => self::deepClone($columns),
                    'species' => self::deepClone($speciesCols)
                ];
            $specId = in_array($item['species_id'], [9, 25]) ? $item['species_id'] : 0;
            if (!isset($res['areas'][$item['area_id']]['districts'][$item['district_id']]['organizations'][$item['org_id']]['species'][$specId]))
                $res['areas'][$item['area_id']]['districts'][$item['district_id']]['organizations'][$item['org_id']]['species'] = self::deepClone($speciesCols);
            $status = null;
            switch ($item['status']) {
                case 'F':
                    $status = 'F';
                    break;
                case 'A':
                    $status = $item['cancel_initiator'] == 'OWNER' ? 'AOW' : 'AOR';
                    break;
                case 'T':
                    $status = 'T';
                    break;
                case 'C':
                    if ($item['etp_status'] == 1053) $status = 'TOR';
                    if ($item['etp_status'] == 8021) $status = 'TOW';
            }
            $res['count']['total']['count']++;
            if ($status) $res['count'][$status]['count']++;
            $res['areas'][$item['area_id']]['count']['total']['count']++;
            if ($status) $res['areas'][$item['area_id']]['count'][$status]['count']++;
            $res['areas'][$item['area_id']]['districts'][$item['district_id']]['count']['total']['count']++;
            if ($status) $res['areas'][$item['area_id']]['districts'][$item['district_id']]['count'][$status]['count']++;
            $res['areas'][$item['area_id']]['districts'][$item['district_id']]['organizations'][$item['org_id']]['count']['total']['count']++;
            if ($status) $res['areas'][$item['area_id']]['districts'][$item['district_id']]['organizations'][$item['org_id']]['count'][$status]['count']++;
            if ($item['has_cats']) {
                $res['areas'][$item['area_id']]['districts'][$item['district_id']]['organizations'][$item['org_id']]['species'][9]['count']['total']['count']++;
                if ($status) $res['areas'][$item['area_id']]['districts'][$item['district_id']]['organizations'][$item['org_id']]['species'][9]['count'][$status]['count']++;
            }
            if ($item['has_dogs']) {
                $res['areas'][$item['area_id']]['districts'][$item['district_id']]['organizations'][$item['org_id']]['species'][25]['count']['total']['count']++;
                if ($status) $res['areas'][$item['area_id']]['districts'][$item['district_id']]['organizations'][$item['org_id']]['species'][25]['count'][$status]['count']++;
            }
            if ($item['has_other']) {
                $res['areas'][$item['area_id']]['districts'][$item['district_id']]['organizations'][$item['org_id']]['species'][0]['count']['total']['count']++;
                if ($status) $res['areas'][$item['area_id']]['districts'][$item['district_id']]['organizations'][$item['org_id']]['species'][0]['count'][$status]['count']++;
            }
        }
        $res['count'] = array_values($res['count']);
        $res['areas'] = array_values($res['areas']);
        foreach ($res['areas'] as $idx => $area) {
            $res['areas'][$idx]['count'] = array_values($area['count']);
            $res['areas'][$idx]['districts'] = array_values($area['districts']);
            foreach ($res['areas'][$idx]['districts'] as $dIdx => $district) {
                $res['areas'][$idx]['districts'][$dIdx]['count'] = array_values($district['count']);
                $res['areas'][$idx]['districts'][$dIdx]['organizations'] = array_values($district['organizations']);
                foreach ($res['areas'][$idx]['districts'][$dIdx]['organizations'] as $oIdx => $org) {
                    $res['areas'][$idx]['districts'][$dIdx]['organizations'][$oIdx]['count'] = array_values($org['count']);
                    $res['areas'][$idx]['districts'][$dIdx]['organizations'][$oIdx]['species'] = array_values($org['species']);
                    foreach ($res['areas'][$idx]['districts'][$dIdx]['organizations'][$oIdx]['species'] as $specIdx => $species) {
                        $res['areas'][$idx]['districts'][$dIdx]['organizations'][$oIdx]['species'][$specIdx]['count'] = array_values($species['count']);
                    }
                }
            }
        }
        return $excel ? $this->generateExcelDetailReport($res) : ['is_success' => true, 'data' => $res];
    }

    private function generateExcelDetailReport($res)
    {
        static::setExcelHeaders();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getDefaultRowDimension()->setRowHeight(40);
        $columnCount = sizeof($res['count']) + 1;
        for ($i = 1; $i <= $columnCount; ++$i) $sheet->getColumnDimensionByColumn($i)->setWidth(20);
        $sheet->mergeCellsByColumnAndRow(1, 1, $columnCount, 1);
        $sheet->setCellValueByColumnAndRow(1, 1, "Детальный отчёт по приемам");
        $sheet->setCellValueByColumnAndRow(1, 2, "Вид животного");
        foreach ($res['count'] as $idx => $column) $sheet->setCellValueByColumnAndRow($idx + 2, 2, $column['name']);
        $row = 2;
        foreach ($res['areas'] as $area) {
            ++$row;
            $sheet->setCellValueByColumnAndRow(1, $row, $area['name']);
            $sheet->mergeCellsByColumnAndRow(1, $row, $columnCount, $row);
            foreach ($area['districts'] as $district) {
                ++$row;
                $sheet->setCellValueByColumnAndRow(1, $row, $district['name']);
                $sheet->mergeCellsByColumnAndRow(1, $row, $columnCount, $row);
                foreach ($district['organizations'] as $org) {
                    ++$row;
                    $sheet->setCellValueByColumnAndRow(1, $row, $org['name']);
                    $sheet->mergeCellsByColumnAndRow(1, $row, $columnCount, $row);
                    foreach ($org['species'] as $spec) {
                        ++$row;
                        $sheet->setCellValueByColumnAndRow(1, $row, $spec['name']);
                        foreach ($spec['count'] as $idx => $col) {
                            $sheet->setCellValueByColumnAndRow(2 + $idx, $row, $col['count']);
                        }
                    }
                }
            }
        }
        $row++;
        $sheet->setCellValueByColumnAndRow(1, $row, "Итого");
        foreach ($res['count'] as $idx => $column) $sheet->setCellValueByColumnAndRow($idx + 2, $row, $column['count']);
        $sheet->getStyleByColumnAndRow(1, 1, $columnCount, $row)->applyFromArray(static::$CELL_STYLE);
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();
        Yii::$app->response->content = $content;
        return Yii::$app->response;
    }

    private function generateExcelReport($res)
    {
        static::setExcelHeaders();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getDefaultRowDimension()->setRowHeight(40);
        $columnCount = sizeof($res['count']) + 1;
        for ($i = 1; $i <= $columnCount; ++$i) $sheet->getColumnDimensionByColumn($i)->setWidth(20);
        $sheet->getColumnDimensionByColumn(1)->setWidth(30);
        $sheet->mergeCellsByColumnAndRow(1, 1, $columnCount, 1);
        $sheet->setCellValueByColumnAndRow(1, 1, "Общий отчёт по приемам");
        $sheet->setCellValueByColumnAndRow(1, 2, "Канал записи");
        $sheet->setCellValueByColumnAndRow(2, 2, "Статус записей");
        $sheet->mergeCellsByColumnAndRow(1, 2, 1, 3);
        $sheet->mergeCellsByColumnAndRow(2, 2, $columnCount, 2);
        foreach ($res['count'] as $idx => $column) $sheet->setCellValueByColumnAndRow($idx + 2, 3, $column['name']);
        $row = 3;
        foreach ($res['areas'] as $area) {
            ++$row;
            $sheet->setCellValueByColumnAndRow(1, $row, $area['name']);
            $sheet->mergeCellsByColumnAndRow(1, $row, $columnCount, $row);
            foreach ($area['districts'] as $district) {
                ++$row;
                $sheet->setCellValueByColumnAndRow(1, $row, $district['name']);
                $sheet->mergeCellsByColumnAndRow(1, $row, $columnCount, $row);
                foreach ($district['organizations'] as $org) {
                    ++$row;
                    $sheet->setCellValueByColumnAndRow(1, $row, $org['name']);
                    $sheet->mergeCellsByColumnAndRow(1, $row, $columnCount, $row);
                    foreach ($org['channels'] as $channel) {
                        ++$row;
                        $sheet->setCellValueByColumnAndRow(1, $row, $channel['name']);
                        foreach ($channel['count'] as $idx => $col) {
                            $sheet->setCellValueByColumnAndRow(2 + $idx, $row, $col['count']);
                        }
                    }
                    ++$row;
                    $sheet->setCellValueByColumnAndRow(1, $row, "Итого по организации");
                    foreach ($org['count'] as $idx => $col) $sheet->setCellValueByColumnAndRow(2 + $idx, $row, $col['count']);
                }
                ++$row;
                $sheet->setCellValueByColumnAndRow(1, $row, "Итого по району");
                foreach ($district['count'] as $idx => $col) $sheet->setCellValueByColumnAndRow(2 + $idx, $row, $col['count']);
            }
            ++$row;
            $sheet->setCellValueByColumnAndRow(1, $row, "Итого по округу");
            foreach ($area['count'] as $idx => $col) $sheet->setCellValueByColumnAndRow(2 + $idx, $row, $col['count']);
        }
        ++$row;
        $sheet->setCellValueByColumnAndRow(1, $row, "Итого");
        foreach ($res['count'] as $idx => $column) $sheet->setCellValueByColumnAndRow($idx + 2, $row, $column['count']);
        $sheet->getStyleByColumnAndRow(1, 1, $columnCount, $row)->applyFromArray(static::$CELL_STYLE);
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();
        Yii::$app->response->content = $content;
        return Yii::$app->response;
    }
}
