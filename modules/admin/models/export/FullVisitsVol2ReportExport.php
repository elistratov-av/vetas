<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 24.07.19
 * Time: 14:24
 */

namespace app\modules\admin\models\export;


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class FullVisitsVol2ReportExport
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

    const STYLE_ORGGREY = [ //светло-серая заливка (далее, каждый стиль с более темным оттенком серого)
        'font' => [
            'bold' => true,
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => [
                'argb' => 'ABABAB',
            ],
        ]
    ];

    const STYLE_DISTGREY = [
        'font' => [
            'bold' => true,
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => [
                'argb' => '808080',
            ],
        ]
    ];

    const STYLE_AREAGREY = [
        'font' => [
            'bold' => true,
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => [
                'argb' => '656565',
            ],
        ]
    ];

    const STYLE_TOTALGRAY = [
        'font' => [
            'bold' => true,
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => [
                'argb' => '5F5F5F',
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
        $currentDistId = false;
        $currentOrgId = false;
        $totals = [
            'totalVisitsPerOrg' => 0,
            'totalVisitsPerDist' => 0,
            'totalVisitsPerArea' => 0,
            'totalVisits' => 0,

            'totalFinishedPerOrg' => 0,
            'totalFinishedPerDist' => 0,
            'totalFinishedPerArea' => 0,
            'totalFinished' => 0,

            'totalCancelledByOrgPerOrg' => 0,
            'totalCancelledByOrgPerDist' => 0,
            'totalCancelledByOrgPerArea' => 0,
            'totalCancelledByOrg' => 0,

            'totalCancelledByOwnerPerOrg' => 0,
            'totalCancelledByOwnerPerDist' => 0,
            'totalCancelledByOwnerPerArea' => 0,
            'totalCancelledByOwner' => 0,

            'totalTransferredPerOrg' => 0,
            'totalTransferredPerDist' => 0,
            'totalTransferredPerArea' => 0,
            'totalTransferred' => 0,

            'totalTransferredByOrgPerOrg' => 0,
            'totalTransferredByOrgPerDist' => 0,
            'totalTransferredByOrgPerArea' => 0,
            'totalTransferredByOrg' => 0,

            'totalTransferredByOwnerPerOrg' => 0,
            'totalTransferredByOwnerPerDist' => 0,
            'totalTransferredByOwnerPerArea' => 0,
            'totalTransferredByOwner' => 0,
        ];

        foreach ($this->report as $report_row) { // идем поэлементно по массиву с результатами запроса
            // если данные в текущей орагнизации кончились, выводим итог
            if($currentOrgId != $report_row['id'] && $currentOrgId != false) {
//                var_dump($totals['totalVisitsPerOrg']);
                $this->_renderTotalRow(
                    'Итого по организации',
                    $totals['totalVisitsPerOrg'],
                    $totals['totalFinishedPerOrg'],
                    $totals['totalCancelledByOrgPerOrg'],
                    $totals['totalCancelledByOwnerPerOrg'],
                    $totals['totalTransferredPerOrg'],
                    $totals['totalTransferredByOrgPerOrg'],
                    $totals['totalTransferredByOwnerPerOrg'],
                    self::STYLE_ORGGREY
                );
                $currentOrgId = false;
                $totals['totalVisitsPerOrg'] = 0;
                $totals['totalFinishedPerOrg'] = 0;
                $totals['totalCancelledByOrgPerOrg'] = 0;
                $totals['totalCancelledByOwnerPerOrg'] = 0;
                $totals['totalTransferredPerOrg'] = 0;
                $totals['totalTransferredByOrgPerOrg'] = 0;
                $totals['totalTransferredByOwnerPerOrg'] = 0;
            }

            // если данные в текущем районе кончились, выводим итог
            if($currentDistId !== $report_row['id_district'] && $currentDistId !== false) {
                $this->_renderTotalRow(
                    'Итого по району',
                    $totals['totalVisitsPerDist'],
                    $totals['totalFinishedPerDist'],
                    $totals['totalCancelledByOrgPerDist'],
                    $totals['totalCancelledByOwnerPerDist'],
                    $totals['totalTransferredPerDist'],
                    $totals['totalTransferredByOrgPerDist'],
                    $totals['totalTransferredByOwnerPerDist'],
                    self::STYLE_DISTGREY
                );
                $currentDistId = false;
                $totals['totalVisitsPerDist'] = 0;
                $totals['totalFinishedPerDist'] = 0;
                $totals['totalCancelledByOrgPerDist'] = 0;
                $totals['totalCancelledByOwnerPerDist'] = 0;
                $totals['totalTransferredPerDist'] = 0;
                $totals['totalTransferredByOrgPerDist'] = 0;
                $totals['totalTransferredByOwnerPerDist'] = 0;
            }

            // если данные в текущем округе кончились, выводим итог
            if($currentAreaId !== $report_row['id_area'] && $currentAreaId !== false) {
                $this->_renderTotalRow(
                    'Итого по округу',
                    $totals['totalVisitsPerArea'],
                    $totals['totalFinishedPerArea'],
                    $totals['totalCancelledByOrgPerArea'],
                    $totals['totalCancelledByOwnerPerArea'],
                    $totals['totalTransferredPerArea'],
                    $totals['totalTransferredByOrgPerArea'],
                    $totals['totalTransferredByOwnerPerArea'],
                    self::STYLE_AREAGREY
                );
                $currentAreaId = false;
                $totals['totalVisitsPerArea'] = 0;
                $totals['totalFinishedPerArea'] = 0;
                $totals['totalCancelledByOrgPerArea'] = 0;
                $totals['totalCancelledByOwnerPerArea'] = 0;
                $totals['totalTransferredPerArea'] = 0;
                $totals['totalTransferredByOrgPerArea'] = 0;
                $totals['totalTransferredByOwnerPerArea'] = 0;
            }

            // если начался новый округ, выводим его название
            if($currentAreaId !== $report_row['id_area']) {
                $currentAreaId = $report_row['id_area'];
                $this->_renderHeaderRow(
                    $report_row['area_name'] ?? self::NO_AREA,
                    self::STYLE_AREAGREY
                );
            }

            // если начался новый район, выводим его название
            if($currentDistId !== $report_row['id_district']) {
                $currentDistId = $report_row['id_district'];
                $this->_renderHeaderRow(
                    $report_row['dist_name'] ?? self::NO_DISTRICT,
                    self::STYLE_DISTGREY
                );
            }

            // если началась новая организация, выводим ее название
            if($currentOrgId !== $report_row['id']) {
                $currentOrgId = $report_row['id'];
                $this->_renderHeaderRow(
                    $report_row['short_name'],
                    self::STYLE_ORGGREY
                );
            }

            $this->_renderRow($report_row['spec_name'], $report_row);

            // ведем подсчет
            $totals['totalVisitsPerOrg'] += $report_row['total_visits'];
            $totals['totalFinishedPerOrg'] += $report_row['total_finished'];
            $totals['totalCancelledByOrgPerOrg'] += $report_row['cancelled_by_clinic'];
            $totals['totalCancelledByOwnerPerOrg'] += $report_row['cancelled_by_owner'];
            $totals['totalTransferredPerOrg'] += $report_row['transferred_not_mosru'];
            $totals['totalTransferredByOrgPerOrg'] += $report_row['transferred_by_clinic_mosru'];
            $totals['totalTransferredByOwnerPerOrg'] += $report_row['transferred_by_owner_mosru'];

            $totals['totalVisitsPerDist'] += $report_row['total_visits'];
            $totals['totalFinishedPerDist'] += $report_row['total_finished'];
            $totals['totalCancelledByOrgPerDist'] += $report_row['cancelled_by_clinic'];
            $totals['totalCancelledByOwnerPerDist'] += $report_row['cancelled_by_owner'];
            $totals['totalTransferredPerDist'] += $report_row['transferred_not_mosru'];
            $totals['totalTransferredByOrgPerDist'] += $report_row['transferred_by_clinic_mosru'];
            $totals['totalTransferredByOwnerPerDist'] += $report_row['transferred_by_owner_mosru'];

            $totals['totalVisitsPerArea'] += $report_row['total_visits'];
            $totals['totalFinishedPerArea'] += $report_row['total_finished'];
            $totals['totalCancelledByOrgPerArea'] += $report_row['cancelled_by_clinic'];
            $totals['totalCancelledByOwnerPerArea'] += $report_row['cancelled_by_owner'];
            $totals['totalTransferredPerArea'] += $report_row['transferred_not_mosru'];
            $totals['totalTransferredByOrgPerArea'] += $report_row['transferred_by_clinic_mosru'];
            $totals['totalTransferredByOwnerPerArea'] += $report_row['transferred_by_owner_mosru'];

            $totals['totalVisits'] += $report_row['total_visits'];
            $totals['totalFinished'] += $report_row['total_finished'];
            $totals['totalCancelledByOrg'] += $report_row['cancelled_by_clinic'];
            $totals['totalCancelledByOwner'] += $report_row['cancelled_by_owner'];
            $totals['totalTransferred'] += $report_row['transferred_not_mosru'];
            $totals['totalTransferredByOrg'] += $report_row['transferred_by_clinic_mosru'];
            $totals['totalTransferredByOwner'] += $report_row['transferred_by_owner_mosru'];
        }

        /*
         * Итог по последней организации
         */
        $this->_renderTotalRow(
            'Итого по организации',
            $totals['totalVisitsPerOrg'],
            $totals['totalFinishedPerOrg'],
            $totals['totalCancelledByOrgPerOrg'],
            $totals['totalCancelledByOwnerPerOrg'],
            $totals['totalTransferredPerOrg'],
            $totals['totalTransferredByOrgPerOrg'],
            $totals['totalTransferredByOwnerPerOrg'],
            self::STYLE_ORGGREY
        );

        /*
         * Итог по последнему району
         */
        $this->_renderTotalRow(
            'Итого по району',
            $totals['totalVisitsPerDist'],
            $totals['totalFinishedPerDist'],
            $totals['totalCancelledByOrgPerDist'],
            $totals['totalCancelledByOwnerPerDist'],
            $totals['totalTransferredPerDist'],
            $totals['totalTransferredByOrgPerDist'],
            $totals['totalTransferredByOwnerPerDist'],
            self::STYLE_DISTGREY
        );

        /*
         * Итог по последнему округу
         */
        $this->_renderTotalRow(
            'Итого по округу',
            $totals['totalVisitsPerArea'],
            $totals['totalFinishedPerArea'],
            $totals['totalCancelledByOrgPerArea'],
            $totals['totalCancelledByOwnerPerArea'],
            $totals['totalTransferredPerArea'],
            $totals['totalTransferredByOrgPerArea'],
            $totals['totalTransferredByOwnerPerArea'],
            self::STYLE_AREAGREY
        );

        /*
         * ВСЕГО
         */
        $this->_renderTotalRow(
            'Итого по округу',
            $totals['totalVisits'],
            $totals['totalFinished'],
            $totals['totalCancelledByOrg'],
            $totals['totalCancelledByOwner'],
            $totals['totalTransferred'],
            $totals['totalTransferredByOrg'],
            $totals['totalTransferredByOwner'],
            self::STYLE_AREAGREY
        );
    }

    /**
     * @param $title
     * @param $report_row
     * @param bool $style
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function _renderRow($title, $report_row, $style = false)
    {
        $sheet = $this->spreadsheet->getActiveSheet();

        $sheet->fromArray(
            [
                $title,
                $report_row['total_visits'],
                $report_row['total_finished'],
                $report_row['cancelled_by_clinic'],
                $report_row['cancelled_by_owner'],
                $report_row['transferred_not_mosru'],
                $report_row['transferred_by_clinic_mosru'],
                $report_row['transferred_by_owner_mosru'],
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
     * @param $totalVar1-7
     * @param bool $style
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function _renderTotalRow($title, $totalVar1, $totalVar2, $totalVar3, $totalVar4, $totalVar5,
                                       $totalVar6, $totalVar7, $style = FALSE)
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
                $totalVar7
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

        $sheet->mergeCells('A' . $this->currentRowNum . ':H' . $this->currentRowNum);

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
            ->getColumnDimension('A');

        $sheet
            ->getRowDimension('1')
            ->setRowHeight(30);

        $message = 'Детальный отчет по приемам за период с ' . $from . ' по ' . $to;
        $sheet->setCellValueByColumnAndRow(
            '1',
            $this->currentRowNum,
            $message
        );

        $sheet
            ->getRowDimension('2')
            ->setRowHeight(45);

        $sheet
            ->getColumnDimension('A')
            ->setWidth(40);

        $sheet
            ->getColumnDimension('B')
            ->setWidth(20);

        $sheet
            ->setCellValue('B2', 'Всего');

        $sheet
            ->getColumnDimension('C')
            ->setWidth(20);

        $sheet
            ->setCellValue('C2', 'Проведено');

        $sheet
            ->getColumnDimension('D')
            ->setWidth(20);

        $sheet
            ->setCellValue('D2', 'Отменено организацией');

        $sheet
            ->getColumnDimension('E')
            ->setWidth(20);

        $sheet
            ->setCellValue('E2', 'Отменено владельцем');

        $sheet
            ->getColumnDimension('F')
            ->setWidth(20);

        $sheet
            ->setCellValue('F2', 'Перенесено');

        $sheet
            ->getColumnDimension('G')
            ->setWidth(25);

        $sheet
            ->setCellValue('G2', 'Перенесено организацией (только для mos.ru)');

        $sheet
            ->getColumnDimension('H')
            ->setWidth(25);

        $sheet
            ->setCellValue('H2', 'Перенесено владельцем (только для mos.ru)');

        $sheet
            ->getStyle('A2:H2')
            ->getAlignment()->setWrapText(true); // Автоперенос

        $sheet
            ->getStyle('A1:H2')
            ->applyFromArray(self::STYLE_HEADER);

        // Передвигаем свой указатель на строку ниже
        $this->currentRowNum = 3;
    }
}