<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 17.07.19
 * Time: 16:01
 */

namespace app\modules\admin\models\export;


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class EmployeesReportExport
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

    const COL_COUNT = 5;


    /**
     * @param $report
     * @param null $filename
     * @param $from
     * @param $to
     * @throws \PhpOffice\PhpSpreadsheet\Exception
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
        $currentOrgId = false;
        $totals = [
            'totalVisitsPerOrg' => 0,
            'totalServicesPerOrg' => 0,
            'totalAmountPerOrg' => 0,
            'totalFreeServicesPerOrg' => 0,
            'totalVisitsPerArea' => 0,
            'totalServicesPerArea' => 0,
            'totalAmountPerArea' => 0,
            'totalFreeServicesPerArea' => 0,
            'totalVisits' => 0,
            'totalServices' => 0,
            'totalAmount' => 0,
            'totalFreeServices' => 0,
        ];


        foreach ($this->report as $report_row) { // идем поэлементно по массиву с результатами запроса
            // если данные в текущей организации кончились, выводим итог
            if($currentOrgId !== $report_row['id_organization'] && $currentOrgId !== false) {
                $this->_renderTotalRow(
                    'Итого по организации',
                    $totals['totalVisitsPerOrg'],
                    $totals['totalServicesPerOrg'],
                    $totals['totalAmountPerOrg'],
                    $totals['totalFreeServicesPerOrg'],
                    self::STYLE_LIGHTGREY
                );
                $currentOrgId = false;
                $totals['totalVisitsPerOrg'] = 0;
                $totals['totalServicesPerOrg'] = 0;
                $totals['totalAmountPerOrg'] = 0;
                $totals['totalFreeServicesPerOrg'] = 0;
            }

            // если данные в текущем округе кончились, выводим итог
            if($currentAreaId !== $report_row['id_area'] && $currentAreaId !== false) {
                $this->_renderTotalRow(
                    'Итого по округу',
                    $totals['totalVisitsPerArea'],
                    $totals['totalServicesPerArea'],
                    $totals['totalAmountPerArea'],
                    $totals['totalFreeServicesPerArea'],
                    self::STYLE_MIDGREY
                );
                $currentAreaId = false;
                $totals['totalVisitsPerArea'] = 0;
                $totals['totalServicesPerArea'] = 0;
                $totals['totalAmountPerArea'] = 0;
                $totals['totalFreeServicesPerArea'] = 0;
            }

            // если начался новый округ, выводим его название
            if($currentAreaId !== $report_row['id_area']) {
                $currentAreaId = $report_row['id_area'];
                $this->_renderHeaderRow(
                    $report_row['name'] ?? self::NO_AREA,
                    self::STYLE_MIDGREY
                );
            }

            // если началась новая организация, выводим ее название
            if($currentOrgId !== $report_row['id_organization']) {
                $currentOrgId = $report_row['id_organization'];
                $this->_renderHeaderRow(
                    $report_row['short_name'],
                    self::STYLE_LIGHTGREY
                );
            }

            $this->_renderRow($report_row['fullname'], $report_row);

            // ведем подсчет по орг
            $totals['totalVisitsPerOrg'] += $report_row['total_visits'];
            $totals['totalServicesPerOrg'] += $report_row['total_services'];
            $totals['totalAmountPerOrg'] += $report_row['total_amount'];
            $totals['totalFreeServicesPerOrg'] += $report_row['total_free_services'];

            // ведем подсчет по округу
            $totals['totalVisitsPerArea'] += $report_row['total_visits'];
            $totals['totalServicesPerArea'] += $report_row['total_services'];
            $totals['totalAmountPerArea'] += $report_row['total_amount'];
            $totals['totalFreeServicesPerArea'] += $report_row['total_free_services'];

            // ведем общий подсчет
            $totals['totalVisits'] += $report_row['total_visits'];
            $totals['totalServices'] += $report_row['total_services'];
            $totals['totalAmount'] += $report_row['total_amount'];
            $totals['totalFreeServices'] += $report_row['total_free_services'];
        }

        /*
         * Итог по последней организации
         */
        $this->_renderTotalRow(
            'Итого по организации',
            $totals['totalVisitsPerOrg'],
            $totals['totalServicesPerOrg'],
            $totals['totalAmountPerOrg'],
            $totals['totalFreeServicesPerOrg'],
            self::STYLE_LIGHTGREY
        );

        /*
         * Итог по последнему округу
         */
        $this->_renderTotalRow(
            'Итого по округу',
            $totals['totalVisitsPerArea'],
            $totals['totalServicesPerArea'],
            $totals['totalAmountPerArea'],
            $totals['totalFreeServicesPerArea'],
            self::STYLE_LIGHTGREY
        );

        /*
         * ВСЕГО
         */
        $this->_renderTotalRow(
            'ВСЕГО',
            $totals['totalVisits'],
            $totals['totalServices'],
            $totals['totalAmount'],
            $totals['totalFreeServices'],
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
                $report_row['total_visits'],
                $report_row['total_services'],
                $report_row['total_amount'],
                $report_row['total_free_services'],
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
     * @param $totalVar1
     * @param $totalVar2
     * @param $totalVar3
     * @param $totalVar4
     * @param bool $style
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function _renderTotalRow($title, $totalVar1, $totalVar2, $totalVar3, $totalVar4, $style = FALSE)
    {
        $sheet = $this->spreadsheet->getActiveSheet();

        $sheet->fromArray(
            [
                $title,
                $totalVar1,
                $totalVar2,
                $totalVar3,
                $totalVar4,
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
            ->mergeCells('A1:E1')
            ->getColumnDimension('A')
            ->setAutoSize(true); // Авто-ширина

        $sheet
            ->getRowDimension('1')
            ->setRowHeight(30);

        $message = 'Отчет по работе сотрудников с ' . $from . ' по ' . $to;
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
            ->setWidth(16);

        $sheet
            ->getRowDimension('2')
            ->setRowHeight(30); // Высота

        $sheet
            ->getStyle('A2:E2')
            ->getAlignment()->setWrapText(true); // Автоперенос

        $sheet
            ->getStyle('A1:E2')
            ->applyFromArray(self::STYLE_HEADER);

        $sheet
            ->getStyle('A:A')
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_TEXT)
        ;

        $sheet
            ->getStyle('D:D')
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1)
        ;

        $sheet
            ->setCellValue('A2', 'ФИО врача');

        $sheet
            ->setCellValue('B2', 'Приемы (количество)');

        $sheet
            ->setCellValue('C2', 'Услуги (количество)');

        $sheet
            ->setCellValue('D2', 'Стоимость приемов');

        $sheet
            ->setCellValue('E2', 'Безвозмездные услуги');

        // Передвигаем свой указатель на строку ниже
        $this->currentRowNum = 3;
    }
}