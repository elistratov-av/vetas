<?php

namespace app\modules\v3\modules\analytics\controllers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use yii\caching\Cache;
use yii\validators\DateValidator;
use yii\web\Controller;

class ServiceController extends BaseController
{

    private static function getServicesInfo($from, $to)
    {
        $ranges = Yii::$app->cache->get("serviceReportRanges");
        if ($ranges) {
            foreach ($ranges as $idx => $range) {
                if (strtotime($range->from) <= strtotime($from) && strtotime($range->to) >= strtotime($to)) {
                    if ($data = Yii::$app->cache->get("services_{$from}_{$to}")) return $data;
                    else unset($ranges[$idx]);
                }
            }
        } else $ranges = [];
        $data = Yii::$app->db->createCommand("SELECT gs.name AS service_name, vgs.id_visit, vgs.id_service as service_id, a.name area_name, a.id area_id, o.id organization_id, o.name organization_name, st.id as channel_id, st.description as channel_name,
        COALESCE(LOWER(v.time_range), v.created_at) AS date, m.service_number, vgs.count, sp.id as specialist_id, v.status as status, p.id_species as species_id, v.type
        FROM visits_gov_services vgs
        INNER JOIN visits v ON v.id = vgs.id_visit
        INNER JOIN visit_pets vp ON v.id = vp.id_visit
        LEFT JOIN pets p ON p.id = vp.id_pet
        LEFT JOIN gov_services gs ON vgs.id_service = gs.id
        LEFT JOIN etp.message_v2 m ON m.visit_id = v.id
        LEFT JOIN organizations o ON o.id = v.id_organization
        LEFT JOIN fias_addresses fa ON fa.id = o.id_fias_address
        LEFT JOIN areas a ON a.id = fa.id_area
        LEFT JOIN districts d ON d.id = fa.id_district
        LEFT JOIN shift_type st ON st.id = v.channel
        LEFT JOIN visits_specialists vs ON vs.id_visit = v.id
        LEFT JOIN specialists sp ON sp.id = vs.id_specialist
        LEFT JOIN users u ON u.id = sp.id_user
        WHERE p.id_species IS NOT NULL AND ((LOWER(v.time_range) >= '$from' AND LOWER(v.time_range) < '$to') OR v.time_range IS NULL)
        AND ((v.created_at >= '$from' AND v.created_at < '$to') OR v.created_at IS NULL)")->queryAll();
        Yii::$app->cache->set("services_{$from}_{$to}", $data, $to == date("Y-m-d") ? 3600 : 3600 * 24 * 7);
        $ranges[] = (object)[
            "from" => $from,
            "to" => $to
        ];
        Yii::$app->cache->set("serviceReportRanges", $ranges, 3600 * 24 * 7 * 30);
        return $data;
    }

    public function actionReport()
    {
        $request = Yii::$app->request;
        $from = trim($request->post('from'));
        $to = trim($request->post('to'));
        $areas = $request->post('areas') !== null ? json_decode($request->post('areas')) : null;
        $organizations = $request->post('organizations') !== null ? json_decode($request->post('organizations')) : null;
        $specialists = $request->post('specialists') !== null ? json_decode($request->post('specialists')) : null;
        $services = $request->post('services') !== null ? json_decode($request->post('services')) : null;
        $statuses = $request->post('statuses') !== null ? json_decode($request->post('statuses')) : null;
        $excel = $request->post('excel', false);
        $excel = !$excel || strtolower($excel) == 'false' || $excel == '0' ? false : true;
        $validator = new DateValidator();
        if (!$validator->validate($from) || !$validator->validate($to)) {
            return ['error' => 'Invalid date format'];
        }
        $data = self::getServicesInfo($from, $to);
        $channels = self::getChannels();

        $res = (object)[
            'count' => 0,
            'channels' => static::deepClone($channels),
            'areas' => []
        ];
        $allAreas = self::getAreas();
        $allOrganizations = self::getOrgs();
        foreach ($allAreas as $area) {
            if ($areas && !in_array($area['id'], $areas)) continue;
            $res->areas[$area['id']] = (object)[
                'id' => $area['id'],
                'name' => $area['name'],
                'count' => 0,
                'channels' => static::deepClone($channels),
                'organizations' => []
            ];
        }
        foreach ($allOrganizations as $org) {
            if (!$org['area_id']) continue;
            if ($organizations && !in_array($org['id'], $organizations)) continue;
            $res->areas[$org['area_id']]->organizations[$org['id']] = (object)[
                'id' => $org['id'],
                'name' => $org['name'],
                'count' => 0,
                'channels' => static::deepClone($channels),
                'services' => []
            ];
        }
        foreach ($data as $item) {
            if (strtotime($item['date']) > strtotime($to) || strtotime($item['date']) < strtotime($from)) continue;
            if ($areas && !in_array($item['area_id'], $areas)) continue;
            if ($organizations && !in_array($item['organization_id'], $organizations)) continue;
            if ($specialists && !in_array($item['specialist_id'], $specialists) && !$item['specialist_id']) continue;
            if ($services && !in_array($item['service_id'], $services)) continue;
            if ($statuses && !in_array($item['status'], $statuses)) continue;
            if (!isset($res->channels[$item['channel_id']]))
                $res->channels[$item['channel_id']] = (object)[
                    'id' => $item['channel_id'],
                    'name' => $item['channel_name'],
                    'count' => 0
                ];
            if (!isset($res->areas[$item['area_id']]))
                $res->areas[$item['area_id']] = (object)[
                    'id' => $item['area_id'],
                    'name' => $item['area_name'],
                    'count' => 0,
                    'channels' => static::deepClone($channels),
                    'organizations' => []
                ];
            if (!isset($res->areas[$item['area_id']]->channels[$item['channel_id']]))
                $res->areas[$item['area_id']]->channels[$item['channel_id']] = (object)[
                    'id' => $item['channel_id'],
                    'count' => 0
                ];
            if (!isset($res->areas[$item['area_id']]->organizations[$item['organization_id']]))
                $res->areas[$item['area_id']]->organizations[$item['organization_id']] = (object)[
                    'id' => $item['organization_id'],
                    'name' => $item['organization_name'],
                    'count' => 0,
                    'channels' => static::deepClone($channels),
                    'services' => []
                ];
            if (!isset($res->areas[$item['area_id']]->organizations[$item['organization_id']]->channels[$item['channel_id']]))
                $res->areas[$item['area_id']]->organizations[$item['organization_id']]->channels[$item['channel_id']] = (object)[
                    'id' => $item['channel_id'],
                    'count' => 0
                ];
            if (!isset($res->areas[$item['area_id']]->organizations[$item['organization_id']]->services[$item['service_id']]))
                $res->areas[$item['area_id']]->organizations[$item['organization_id']]->services[$item['service_id']] = (object)[
                    'id' => $item['channel_id'],
                    'name' => $item['service_name'],
                    'channels' => static::deepClone($channels),
                    'count' => 0
                ];
            if (!isset($res->areas[$item['area_id']]->organizations[$item['organization_id']]->services[$item['service_id']]->channels[$item['channel_id']]))
                $res->areas[$item['area_id']]->organizations[$item['organization_id']]->services[$item['service_id']]->channels[$item['channel_id']] = (object)[
                    'id' => $item['channel_id'],
                    'count' => 0
                ];
            $res->count++;
            $res->channels[$item['channel_id']]->count++;
            $res->areas[$item['area_id']]->count++;
            $res->areas[$item['area_id']]->channels[$item['channel_id']]->count++;
            $res->areas[$item['area_id']]->organizations[$item['organization_id']]->count++;
            $res->areas[$item['area_id']]->organizations[$item['organization_id']]->channels[$item['channel_id']]->count++;
            $res->areas[$item['area_id']]->organizations[$item['organization_id']]->services[$item['service_id']]->count++;
            $res->areas[$item['area_id']]->organizations[$item['organization_id']]->services[$item['service_id']]->channels[$item['channel_id']]->count++;
        }
        $res->channels = array_values($res->channels);
        $res->areas = array_values($res->areas);
        foreach ($res->areas as $idx => $area) {
            $res->areas[$idx]->channels = array_values($res->areas[$idx]->channels);
            $res->areas[$idx]->organizations = array_values($res->areas[$idx]->organizations);
            foreach ($res->areas[$idx]->organizations as $orIdx => $organization) {
                $res->areas[$idx]->organizations[$orIdx]->channels = array_values($res->areas[$idx]->organizations[$orIdx]->channels);
                $res->areas[$idx]->organizations[$orIdx]->services = array_values($res->areas[$idx]->organizations[$orIdx]->services);
                foreach ($res->areas[$idx]->organizations[$orIdx]->services as $serIdx => $service) {
                    $res->areas[$idx]->organizations[$orIdx]->services[$serIdx]->channels = array_values($res->areas[$idx]->organizations[$orIdx]->services[$serIdx]->channels);
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
        $columnCount = count($res->channels) + 2;
        $sheet->mergeCellsByColumnAndRow(1, 1, $columnCount, 1);
        $sheet->mergeCellsByColumnAndRow(1, 2, 1, 3);
        $sheet->mergeCellsByColumnAndRow(2, 2, $columnCount, 2);
        $sheet->setCellValueByColumnAndRow(1, 1, "Общий отчёт по услугам");
        $sheet->setCellValueByColumnAndRow(1, 2, "Наименование услуги");
        $sheet->setCellValueByColumnAndRow(2, 2, "Количество записей");
        foreach ($res->channels as $idx => $channel) {
            $sheet->setCellValueByColumnAndRow($idx + 2, 3, $channel->name);
            $sheet->getColumnDimensionByColumn($idx + 2)->setWidth(15);
        }
        $sheet->setCellValueByColumnAndRow($columnCount, 3, "Итого");

        $row = 4;
        foreach ($res->areas as $area) {
            $sheet->mergeCellsByColumnAndRow(1, $row, $columnCount, $row);
            $sheet->setCellValueByColumnAndRow(1, $row, $area->name);
            $row++;
            foreach ($area->organizations as $org) {
                $sheet->mergeCellsByColumnAndRow(1, $row, $columnCount, $row);
                $sheet->setCellValueByColumnAndRow(1, $row, $org->name);
                $row++;
                foreach ($org->services as $service) {
                    $sheet->setCellValueByColumnAndRow(1, $row, $service->name);
                    foreach ($service->channels as $chIdx => $ch) {
                        $sheet->setCellValueByColumnAndRow($chIdx + 2, $row, $ch->count);
                    }
                    $sheet->setCellValueByColumnAndRow($columnCount, $row, $service->count);
                    $row++;
                }
                $sheet->setCellValueByColumnAndRow(1, $row, "Итого по организации");
                foreach ($org->channels as $chIdx => $ch) {
                    $sheet->setCellValueByColumnAndRow($chIdx + 2, $row, $ch->count);
                }
                $sheet->setCellValueByColumnAndRow($columnCount, $row, $org->count);
                $row++;
            }
            $sheet->setCellValueByColumnAndRow(1, $row, "Итого по округу");
            foreach ($area->channels as $chIdx => $ch) {
                $sheet->setCellValueByColumnAndRow($chIdx + 2, $row, $ch->count);
            }
            $sheet->setCellValueByColumnAndRow($columnCount, $row, $area->count);
            $row++;
        }
        $sheet->setCellValueByColumnAndRow(1, $row, "Итого");
        foreach ($res->channels as $chIdx => $ch) {
            $sheet->setCellValueByColumnAndRow($chIdx + 2, $row, $ch->count);
        }
        $sheet->setCellValueByColumnAndRow($columnCount, $row, $res->count);
        $sheet->getStyleByColumnAndRow(1, 1, $columnCount, $row)->applyFromArray(static::$CELL_STYLE);
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();
        Yii::$app->response->content = $content;
        return Yii::$app->response;
    }

    public function actionDetailReport()
    {
        $request = Yii::$app->request;
        $from = trim($request->post('from'));
        $to = trim($request->post('to'));
        $areas = $request->post('areas') !== null ? json_decode($request->post('areas')) : null;
        $organizations = $request->post('organizations') !== null ? json_decode($request->post('organizations')) : null;
        $specialists = $request->post('specialists') !== null ? json_decode($request->post('specialists')) : null;
        $types = $request->post('types') !== null ? json_decode($request->post('types')) : null;
        $channels = $request->post('channels') !== null ? json_decode($request->post('channels')) : null;
        $species = $request->post('species') !== null ? json_decode($request->post('species')) : null;
        $services = $request->post('services') !== null ? json_decode($request->post('services')) : null;
        $excel = $request->post('excel', false);
        $excel = !$excel || strtolower($excel) == 'false' || $excel == '0' ? false : true;
        $validator = new DateValidator();

        if (!$validator->validate($from) || !$validator->validate($to)) {
            return ['error' => 'Invalid date format'];
        }
        $data = self::getServicesInfo($from, $to);
        $statuses = [];
        foreach ((array)static::$STATUSES as $idx => $status) {
            $statuses[$status['id']] = $status;
            $statuses[$status['id']]['count'] = 0;
            $statuses[$status['id']] = (object)$statuses[$status['id']];
        }
        $res = (object)[
            'count' => 0,
            'statuses' => static::deepClone($statuses),
            'areas' => [],
        ];

        $allAreas = self::getAreas();
        $allOrganizations = self::getOrgs();
        foreach ($allAreas as $area) {
            if ($areas && !in_array($area['id'], $areas)) continue;
            $res->areas[$area['id']] = (object)[
                'id' => $area['id'],
                'name' => $area['name'],
                'count' => 0,
                'statuses' => static::deepClone($statuses),
                'organizations' => []
            ];
        }
        foreach ($allOrganizations as $org) {
            if (!$org['area_id']) continue;
            if ($organizations && !in_array($org['id'], $organizations)) continue;
            $res->areas[$org['area_id']]->organizations[$org['id']] = (object)[
                'id' => $org['id'],
                'name' => $org['name'],
                'count' => 0,
                'statuses' => static::deepClone($statuses),
                'channels' => []
            ];
        }

        foreach ($data as $item) {
            if (!$item['status']) continue;
            if (strtotime($item['date']) > strtotime($to) || strtotime($item['date']) < strtotime($from)) continue;
            if ($areas && !in_array($item['area_id'], $areas)) continue;
            if ($organizations && !in_array($item['organization_id'], $organizations)) continue;
            if ($specialists && !in_array($item['specialist_id'], $specialists) && !$item['specialist_id']) continue;
            if ($types && !in_array($item['type'], $types)) continue;
            if ($channels && !in_array($item['channel_id'], $channels)) continue;
            if ($species) {
                if (in_array(0, $species)) {
                    if (!in_array(25, $species) && !in_array(9, $species)) {
                        if (in_array($item['species_id'], [25, 9])) continue;
                    }
                } else {
                    if (!in_array($item['species_id'], $species)) continue;
                }
            }
            if ($services && !in_array($item['service_id'], $services)) continue;
            if (!isset($res->statuses[$item['status']]))
                $res->statuses[$item['status']] = (object)[
                    'id' => $item['status'],
                    'name' => $item['status'],
                    'count' => 0
                ];

            if (!isset($res->areas[$item['area_id']]))
                $res->areas[$item['area_id']] = (object)[
                    'id' => $item['area_id'],
                    'name' => $item['area_name'],
                    'count' => 0,
                    'statuses' => static::deepClone($statuses),
                    'organizations' => []
                ];
            if (!isset($res->areas[$item['area_id']]->statuses[$item['status']]))
                $res->areas[$item['area_id']]->statuses[$item['status']] = (object)[
                    'id' => $item['status'],
                    'count' => 0
                ];
            if (!isset($res->areas[$item['area_id']]->organizations[$item['organization_id']]))
                $res->areas[$item['area_id']]->organizations[$item['organization_id']] = (object)[
                    'id' => $item['organization_id'],
                    'name' => $item['organization_name'],
                    'count' => 0,
                    'statuses' => static::deepClone($statuses),
                    'channels' => []
                ];
            if (!isset($res->areas[$item['area_id']]->organizations[$item['organization_id']]->statuses[$item['status']]))
                $res->areas[$item['area_id']]->organizations[$item['organization_id']]->statuses[$item['status']] = (object)[
                    'id' => $item['status'],
                    'count' => 0
                ];
            if (!isset($res->areas[$item['area_id']]->organizations[$item['organization_id']]->channels[$item['channel_id']]))
                $res->areas[$item['area_id']]->organizations[$item['organization_id']]->channels[$item['channel_id']] = (object)[
                    'id' => $item['channel_id'],
                    'name' => $item['channel_name'],
                    'count' => 0,
                    'statuses' => static::deepClone($statuses),
                    'services' => []
                ];
            if (!isset($res->areas[$item['area_id']]->organizations[$item['organization_id']]->channels[$item['channel_id']]->statuses[$item['status']]))
                $res->areas[$item['area_id']]->organizations[$item['organization_id']]->channels[$item['channel_id']]->statuses[$item['status']] = (object)[
                    'id' => $item['status'],
                    'count' => 0
                ];
            if (!isset($res->areas[$item['area_id']]->organizations[$item['organization_id']]->channels[$item['channel_id']]->services[$item['service_id']]))
                $res->areas[$item['area_id']]->organizations[$item['organization_id']]->channels[$item['channel_id']]->services[$item['service_id']] = (object)[
                    'id' => $item['service_id'],
                    'name' => $item['service_name'],
                    'count' => 0,
                    'statuses' => static::deepClone($statuses),
                ];
            if (!isset($res->areas[$item['area_id']]->organizations[$item['organization_id']]->channels[$item['channel_id']]->services[$item['service_id']]->statuses[$item['status']]))
                $res->areas[$item['area_id']]->organizations[$item['organization_id']]->channels[$item['channel_id']]->services[$item['service_id']]->statuses[$item['status']] = (object)[
                    'id' => $item['status'],
                    'count' => 0
                ];
            $res->count++;
            $res->statuses[$item['status']]->count++;
            $res->areas[$item['area_id']]->count++;
            $res->areas[$item['area_id']]->statuses[$item['status']]->count++;
            $res->areas[$item['area_id']]->organizations[$item['organization_id']]->count++;
            $res->areas[$item['area_id']]->organizations[$item['organization_id']]->statuses[$item['status']]->count++;
            $res->areas[$item['area_id']]->organizations[$item['organization_id']]->channels[$item['channel_id']]->count++;
            $res->areas[$item['area_id']]->organizations[$item['organization_id']]->channels[$item['channel_id']]->statuses[$item['status']]->count++;
            $res->areas[$item['area_id']]->organizations[$item['organization_id']]->channels[$item['channel_id']]->services[$item['service_id']]->count++;
            $res->areas[$item['area_id']]->organizations[$item['organization_id']]->channels[$item['channel_id']]->services[$item['service_id']]->statuses[$item['status']]->count++;
        }
        $res->statuses = array_values($res->statuses);
        $res->areas = array_values($res->areas);
        foreach ($res->areas as $idx => $area) {
            $res->areas[$idx]->statuses = array_values($res->areas[$idx]->statuses);
            $res->areas[$idx]->organizations = array_values($res->areas[$idx]->organizations);
            foreach ($res->areas[$idx]->organizations as $orIdx => $organization) {
                $res->areas[$idx]->organizations[$orIdx]->statuses = array_values($res->areas[$idx]->organizations[$orIdx]->statuses);
                $res->areas[$idx]->organizations[$orIdx]->channels = array_values($res->areas[$idx]->organizations[$orIdx]->channels);
                foreach ($res->areas[$idx]->organizations[$orIdx]->channels as $chId => $channel) {
                    $res->areas[$idx]->organizations[$orIdx]->channels[$chId]->statuses = array_values($res->areas[$idx]->organizations[$orIdx]->channels[$chId]->statuses);
                    $res->areas[$idx]->organizations[$orIdx]->channels[$chId]->services = array_values($res->areas[$idx]->organizations[$orIdx]->channels[$chId]->services);
                    foreach ($res->areas[$idx]->organizations[$orIdx]->channels[$chId]->services as $serId => $service) {
                        $res->areas[$idx]->organizations[$orIdx]->channels[$chId]->services[$serId]->statuses = array_values($res->areas[$idx]->organizations[$orIdx]->channels[$chId]->services[$serId]->statuses);
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
        $sheet->getColumnDimensionByColumn(1)->setWidth(70);
        $sheet->getDefaultRowDimension()->setRowHeight(40);
        $columnCount = count($res->statuses) + 3;
        $sheet->mergeCellsByColumnAndRow(1, 1, $columnCount, 1);
        $sheet->mergeCellsByColumnAndRow(1, 2, 1, 3);
        $sheet->setCellValueByColumnAndRow(1, 1, "Детальный отчёт по услугам");
        $sheet->setCellValueByColumnAndRow(1, 2, "Наименование услуги");
        $sheet->mergeCellsByColumnAndRow(1, 2, 1, 3);
        $sheet->mergeCellsByColumnAndRow(2, 2, 2, 3);
        $sheet->setCellValueByColumnAndRow(2, 2, "Канал записи");
        $sheet->setCellValueByColumnAndRow(3, 2, "Статус записей");
        $sheet->mergeCellsByColumnAndRow(3, 2, $columnCount, 2);
        foreach ($res->statuses as $idx => $status) {
            $sheet->setCellValueByColumnAndRow($idx + 3, 3, $status->name);
            $sheet->getColumnDimensionByColumn($idx + 3)->setWidth(15);
        }
        $sheet->setCellValueByColumnAndRow($columnCount, 3, "Итого");

        $row = 4;
        foreach ($res->areas as $area) {
            $sheet->mergeCellsByColumnAndRow(1, $row, $columnCount, $row);
            $sheet->setCellValueByColumnAndRow(1, $row, $area->name);
            $row++;
            foreach ($area->organizations as $org) {
                $sheet->mergeCellsByColumnAndRow(1, $row, $columnCount, $row);
                $sheet->setCellValueByColumnAndRow(1, $row, $org->name);
                $row++;
                foreach ($org->channels as $channel) {
                    foreach ($channel->services as $service) {
                        $sheet->setCellValueByColumnAndRow(1, $row, $service->name);
                        $sheet->setCellValueByColumnAndRow(2, $row, $channel->name);
                        foreach ($service->statuses as $sIdx => $status) {
                            $sheet->setCellValueByColumnAndRow($sIdx + 3, $row, $status->count);
                        }
                        $sheet->setCellValueByColumnAndRow($columnCount, $row, $service->count);
                        $row++;
                    }
                }
                $sheet->setCellValueByColumnAndRow(1, $row, "Итого по организации");
                foreach ($org->statuses as $sIdx => $status) {
                    $sheet->setCellValueByColumnAndRow($sIdx + 2, $row, $status->count);
                }
                $sheet->setCellValueByColumnAndRow($columnCount, $row, $org->count);
                $row++;
            }
            $sheet->setCellValueByColumnAndRow(1, $row, "Итого по округу");
            foreach ($area->statuses as $sIdx => $status) {
                $sheet->setCellValueByColumnAndRow($sIdx + 2, $row, $status->count);
            }
            $sheet->setCellValueByColumnAndRow($columnCount, $row, $area->count);
            $row++;
        }
        $sheet->setCellValueByColumnAndRow(1, $row, "Итого");
        foreach ($res->statuses as $sIdx => $status) {
            $sheet->setCellValueByColumnAndRow($sIdx + 2, $row, $status->count);
        }
        $sheet->setCellValueByColumnAndRow($columnCount, $row, $res->count);
        $sheet->getStyleByColumnAndRow(1, 1, $columnCount, $row)->applyFromArray(static::$CELL_STYLE);
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();
        Yii::$app->response->content = $content;
        return Yii::$app->response;
    }
}
