<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 16.07.19
 * Time: 10:29
 */

namespace app\modules\admin\models\export;


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ClinicsDutyReportExport
{
    use SendXlsxTrait;

    /**
     * @var Spreadsheet
     */
    protected $spreadsheet;

    /**
     * @var array
     */
    protected $report;

    /**
     * @var int Номер текущей строки
     */
    protected $currentRowNum = 1;

    const STYLE_LIGHTGREY = [
        'font' => [
            'bold' => true,
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => [
                'argb' => 'D3D3D3',
            ],
        ]
    ];

    const STYLE_MIDGREY = [
        'font' => [
            'bold' => true,
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => [
                'argb' => 'BEBEBE',
            ],
        ]
    ];

    const STYLE_DARKGRAY = [
        'font' => [
            'bold' => true,
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => [
                'argb' => 'A9A9A9',
            ],
        ]
    ];

    const STYLE_HEADER = [
        'font' => [
            'bold' => true,
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
    ];

    const NO_AREA = 'Округ не найден';
    const NO_DISTRICT = 'Район не найден';

    const COL_COUNT = 8;

    /**
     * @param $report
     * @param null $filename
     * @param $from
     * @param $to
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\web\RangeNotSatisfiableHttpException
     */
    public function export($report, $filename = null, $from, $to)
    {
        $this->spreadsheet = new Spreadsheet();
        $this->report = $report;

        $this->renderReport($from, $to);

        $this->sendXlsx($this->spreadsheet,$filename);
    }

    /**
     * @param $from
     * @param $to
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function renderReport($from, $to)
    {
        /*
         * Шапка
         */
        $this->renderHead($from, $to);

        $this->renderBody();
    }

    /**
     * циклический вывод строк тела отчета
     */
    protected function renderBody()
    {

        $currentAreaId = false;
        $currentDistId = false;
        $totals = [
            'totalMosruPerDist' => 0,
            'totalMosruPerArea' => 0,
            'totalMosru' => 0,
            'totalPhonePerDist' => 0,
            'totalPhonePerArea' => 0,
            'totalPhone' => 0,
            'totalLiveQueuePerDist' => 0,
            'totalLiveQueuePerArea' => 0,
            'totalLiveQueue' => 0,
            'totalAppointmentPerDist' => 0,
            'totalAppointmentPerArea' => 0,
            'totalAppointment' => 0,
            'totalVisitsPerDist' => 0,
            'totalVisitsPerArea' => 0,
            'totalVisits' => 0,
            'totalServicesPerDist' => 0,
            'totalServicesPerArea' => 0,
            'totalServices' => 0,
        ];


        foreach ($this->report as $report_row) { // идем поэлементно по массиву с результатами запроса
//            если данные в текущем районе кончились, выводим итог
            if($currentDistId !== $report_row['id_district'] && $currentDistId !== false) {
                $this->_renderTotalRow(
                    'Итого по району',
                    $totals['totalMosruPerDist'] + $totals['totalPhonePerDist'] + $totals['totalLiveQueuePerDist'] + $totals['totalAppointmentPerDist'],
                    $totals['totalMosruPerDist'],
                    $totals['totalPhonePerDist'],
                    $totals['totalLiveQueuePerDist'],
                    $totals['totalAppointmentPerDist'],
                    $totals['totalVisitsPerDist'],
                    $totals['totalServicesPerDist'],
                    self::STYLE_LIGHTGREY
                );
                $currentDistId = false;
                $totals['totalMosruPerDist'] = 0;
                $totals['totalPhonePerDist'] = 0;
                $totals['totalLiveQueuePerDist'] = 0;
                $totals['totalAppointmentPerDist'] = 0;
                $totals['totalVisitsPerDist'] = 0;
                $totals['totalServicesPerDist'] = 0;
            }

//            если данные в текущем округе кончились, выводим итог
            if($currentAreaId !== $report_row['id_area'] && $currentAreaId !== false) {
                $this->_renderTotalRow(
                    'Итого по округу',
                    $totals['totalMosruPerArea'] + $totals['totalPhonePerArea'] + $totals['totalLiveQueuePerArea'] + $totals['totalAppointmentPerArea'],
                    $totals['totalMosruPerArea'],
                    $totals['totalPhonePerArea'],
                    $totals['totalLiveQueuePerArea'],
                    $totals['totalAppointmentPerArea'],
                    $totals['totalVisitsPerArea'],
                    $totals['totalServicesPerArea'],
                    self::STYLE_MIDGREY
                );
                $currentAreaId = false;
                $totals['totalMosruPerArea'] = 0;
                $totals['totalPhonePerArea'] = 0;
                $totals['totalLiveQueuePerArea'] = 0;
                $totals['totalAppointmentPerArea'] = 0;
                $totals['totalVisitsPerArea'] = 0;
                $totals['totalServicesPerArea'] = 0;
            }

//            если начался новый округ, выводим его название
            if($currentAreaId !== $report_row['id_area']) {
                $currentAreaId = $report_row['id_area'];
                $this->_renderHeaderRow(
                    $report_row['area_name'] ?? self::NO_AREA,
                    self::STYLE_MIDGREY
                );
            }

//            если начался новый район, выводим его название
            if($currentDistId !== $report_row['id_district']) {
                $currentDistId = $report_row['id_district'];
                $this->_renderHeaderRow(
                    $report_row['dist_name'] ?? self::NO_DISTRICT,
                    self::STYLE_LIGHTGREY
                );
            }

            $this->_renderRow($report_row['short_name'], $report_row);

            // ведем подсчет по району
            $totals['totalMosruPerDist'] += $report_row['mosruVisitsQuery'];
            $totals['totalPhonePerDist'] += $report_row['phoneVisitsQuery'];
            $totals['totalLiveQueuePerDist'] += $report_row['liveQueueVisitsQuery'];
            $totals['totalAppointmentPerDist'] += $report_row['appointmentVisitsQuery'];
            $totals['totalVisitsPerDist'] += $report_row['totalVisitsQuery'];
            $totals['totalServicesPerDist'] += $report_row['servicesCounter'];

            // ведем подсчет по округу
            $totals['totalMosruPerArea'] += $report_row['mosruVisitsQuery'];
            $totals['totalPhonePerArea'] += $report_row['phoneVisitsQuery'];
            $totals['totalLiveQueuePerArea'] += $report_row['liveQueueVisitsQuery'];
            $totals['totalAppointmentPerArea'] += $report_row['appointmentVisitsQuery'];
            $totals['totalVisitsPerArea'] += $report_row['totalVisitsQuery'];
            $totals['totalServicesPerArea'] += $report_row['servicesCounter'];

            // ведем общий подсчет
            $totals['totalMosru'] += $report_row['mosruVisitsQuery'];
            $totals['totalPhone'] += $report_row['phoneVisitsQuery'];
            $totals['totalLiveQueue'] += $report_row['liveQueueVisitsQuery'];
            $totals['totalAppointment'] += $report_row['appointmentVisitsQuery'];
            $totals['totalVisits'] += $report_row['totalVisitsQuery'];
            $totals['totalServices'] += $report_row['servicesCounter'];
        }

        /*
         * Итог по последнему району
         */
        $this->_renderTotalRow(
            'Итого по району',
            $totals['totalMosruPerDist'] + $totals['totalPhonePerDist'] + $totals['totalLiveQueuePerDist'] + $totals['totalAppointmentPerDist'],
            $totals['totalMosruPerDist'],
            $totals['totalPhonePerDist'],
            $totals['totalLiveQueuePerDist'],
            $totals['totalAppointmentPerDist'],
            $totals['totalVisitsPerDist'],
            $totals['totalServicesPerDist'],
            self::STYLE_LIGHTGREY
        );

        /*
         * Итог по последнему округу
         */
        $this->_renderTotalRow(
            'Итого по округу',
            $totals['totalMosruPerArea'] + $totals['totalPhonePerArea'] + $totals['totalLiveQueuePerArea'] + $totals['totalAppointmentPerArea'],
            $totals['totalMosruPerArea'],
            $totals['totalPhonePerArea'],
            $totals['totalLiveQueuePerArea'],
            $totals['totalAppointmentPerArea'],
            $totals['totalVisitsPerArea'],
            $totals['totalServicesPerArea'],
            self::STYLE_LIGHTGREY
        );

        /*
         * ВСЕГО
         */
        $this->_renderTotalRow(
            'ВСЕГО',
            $totals['totalMosru'] + $totals['totalPhone'] + $totals['totalLiveQueue'] + $totals['totalAppointment'],
            $totals['totalMosru'],
            $totals['totalPhone'],
            $totals['totalLiveQueue'],
            $totals['totalAppointment'],
            $totals['totalVisits'],
            $totals['totalServices'],
            self::STYLE_DARKGRAY
        );
    }

    /**
     * @param $title
     * @param $report_row
     * @param bool $style
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function _renderRow($title, $report_row, $style = FALSE)
    {
        $sheet = $this->spreadsheet->getActiveSheet();

        $sheet->fromArray(
            [
                $title,
                $report_row['mosruVisitsQuery'] + $report_row['phoneVisitsQuery'] + $report_row['liveQueueVisitsQuery'] + $report_row['appointmentVisitsQuery'],
                $report_row['mosruVisitsQuery'],
                $report_row['phoneVisitsQuery'],
                $report_row['liveQueueVisitsQuery'],
                $report_row['appointmentVisitsQuery'],
                $report_row['totalVisitsQuery'],
                $report_row['servicesCounter']
            ],
            NULL,
            'A' . $this->currentRowNum,
            true
        );

        // Стиль?
        if (!empty($style)){
            $sheet->getStyleByColumnAndRow(
                1,
                $this->currentRowNum,
                self::COL_COUNT,
                $this->currentRowNum
            )
                ->applyFromArray($style);
        }

        $this->currentRowNum ++;
    }

    /**
     * Выводит строку с итогом
     * @param $title
     * @param $totals
     * @param bool $style
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function _renderTotalRow($title, $totalVar1, $totalVar2, $totalVar3, $totalVar4, $totalVar5, $totalVar6, $totalVar7, $style = FALSE)
    {
        $sheet = $this->spreadsheet->getActiveSheet();

        $sheet->fromArray(
            [
                $title,
                $totalVar1,
                $totalVar2,
                $totalVar3,
                $totalVar4,
                $totalVar5,
                $totalVar6,
                $totalVar7,
            ],
            NULL,
            'A' . $this->currentRowNum,
            true
        );

        // Стиль?
        if (!empty($style)){
            $sheet->getStyleByColumnAndRow(
                1,
                $this->currentRowNum,
                self::COL_COUNT,
                $this->currentRowNum
            )
                ->applyFromArray($style);
        }

        $this->currentRowNum ++;
    }

    /**
     * @param $title
     * @param bool $style
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function _renderHeaderRow($title, $style = FALSE)
    {
        $sheet = $this->spreadsheet->getActiveSheet();

        $sheet->setCellValueByColumnAndRow(
            '1',
            $this->currentRowNum,
            $title
        );

        // Стиль?
        if (!empty($style)){
            $sheet->getStyleByColumnAndRow(
                1,
                $this->currentRowNum,
                self::COL_COUNT,
                $this->currentRowNum
            )
                ->applyFromArray($style);
        }

        $this->currentRowNum ++;
    }


    /**
     * Выводит название отчета с указанием периода и шапку таблицы
     * @param $from
     * @param $to
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function renderHead($from, $to)
    {
        $sheet = $this->spreadsheet->getActiveSheet();

        /*
         * Форматирование
         */
        $sheet
            ->mergeCells('A1:H1')
            ->getColumnDimension('A')
            ->setAutoSize(true); // Авто-ширина

        $sheet
            ->mergeCells('B2:F2');

        $sheet
            ->getRowDimension('1')
            ->setRowHeight(30);

        $message = 'Отчет о нагрузке на ветеринарные учреждения и службы с ' . $from . ' по ' . $to;
        $sheet->setCellValueByColumnAndRow(
            '1',
            $this->currentRowNum,
            $message
        );

        $sheet
            ->getColumnDimension('A')
            ->setAutoSize(true); // Авто-ширина

        $sheet
            ->getColumnDimension('B')
            ->setWidth(15);

        $sheet
            ->getColumnDimension('C')
            ->setWidth(15);

        $sheet
            ->getColumnDimension('D')
            ->setWidth(15);

        $sheet
            ->getColumnDimension('E')
            ->setWidth(15);

        $sheet
            ->getColumnDimension('F')
            ->setWidth(15);

        $sheet
            ->getColumnDimension('G')
            ->setWidth(15);

        $sheet
            ->getColumnDimension('H')
            ->setWidth(15);

        $sheet
            ->getRowDimension('2')
            ->setRowHeight(30); // Высота

        $sheet
            ->getRowDimension('3')
            ->setRowHeight(30); // Высота

        $sheet
            ->getStyle('A2:H3')
            ->getAlignment()->setWrapText(true); // Автоперенос

        $sheet
            ->getStyle('A1:H3')
            ->applyFromArray(self::STYLE_HEADER);

        $sheet
            ->getStyle('A:A')
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_TEXT)
        ;

        $sheet
            ->setCellValue('B2', 'Принято обращений (записей на прием)');

        $sheet
            ->setCellValue('G2', 'Проведено приемов');

        $sheet
            ->setCellValue('H2', 'Оказано услуг');

        $sheet
            ->setCellValue('A3', 'Организация');

        $sheet
            ->setCellValue('B3', 'Записей всего');

        $sheet
            ->setCellValue('C3', 'Mos.ru');

        $sheet
            ->setCellValue('D3', 'Телефон');

        $sheet
            ->setCellValue('E3', 'Живая очередь');

        $sheet
            ->setCellValue('F3', 'Направление');

        // Передвигаем свой указатель на строку ниже
        $this->currentRowNum = 4;
    }
}