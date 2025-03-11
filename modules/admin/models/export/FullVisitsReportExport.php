<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 19.07.19
 * Time: 14:52
 */

namespace app\modules\admin\models\export;


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
class FullVisitsReportExport
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

    const COL_COUNT = 16;


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
        $totals = [
            'totalMosruCreatedPerDist' => 0,
            'totalMosruCancelledPerDist' => 0,
            'totalMosruTransferedPerDist' => 0,
            'totalMosruFinishedPerDist' => 0,
            'totalMosruCreatedPerArea' => 0,
            'totalMosruCancelledPerArea' => 0,
            'totalMosruTransferedPerArea' => 0,
            'totalMosruFinishedPerArea' => 0,
            'totalMosruCreated' => 0,
            'totalMosruCancelled' => 0,
            'totalMosruTransfered' => 0,
            'totalMosruFinished' => 0,

            'totalPhoneCreatedPerDist' => 0,
            'totalPhoneCancelledPerDist' => 0,
            'totalPhoneTransferedPerDist' => 0,
            'totalPhoneFinishedPerDist' => 0,
            'totalPhoneCreatedPerArea' => 0,
            'totalPhoneCancelledPerArea' => 0,
            'totalPhoneTransferedPerArea' => 0,
            'totalPhoneFinishedPerArea' => 0,
            'totalPhoneCreated' => 0,
            'totalPhoneCancelled' => 0,
            'totalPhoneTransfered' => 0,
            'totalPhoneFinished' => 0,

            'totalLqCreatedPerDist' => 0,
            'totalLqCancelledPerDist' => 0,
            'totalLqTransferedPerDist' => 0,
            'totalLqFinishedPerDist' => 0,
            'totalLqCreatedPerArea' => 0,
            'totalLqCancelledPerArea' => 0,
            'totalLqTransferedPerArea' => 0,
            'totalLqFinishedPerArea' => 0,
            'totalLqCreated' => 0,
            'totalLqCancelled' => 0,
            'totalLqTransfered' => 0,
            'totalLqFinished' => 0,

            'totalWdCreatedPerDist' => 0,
            'totalWdCancelledPerDist' => 0,
            'totalWdFinishedPerDist' => 0,
            'totalWdCreatedPerArea' => 0,
            'totalWdCancelledPerArea' => 0,
            'totalWdFinishedPerArea' => 0,
            'totalWdCreated' => 0,
            'totalWdCancelled' => 0,
            'totalWdFinished' => 0
        ];


        foreach ($this->report as $report_row) { // идем поэлементно по массиву с результатами запроса
            // если данные в текущем районе кончились, выводим итог
            if($currentDistId !== $report_row['id_district'] && $currentDistId !== false) {
                $this->_renderTotalRow(
                    'Итого по району',
                    $totals['totalMosruCreatedPerDist'],
                    $totals['totalMosruCancelledPerDist'],
                    $totals['totalMosruTransferedPerDist'],
                    $totals['totalMosruFinishedPerDist'],

                    $totals['totalPhoneCreatedPerDist'],
                    $totals['totalPhoneCancelledPerDist'],
                    $totals['totalPhoneTransferedPerDist'],
                    $totals['totalPhoneFinishedPerDist'],

                    $totals['totalLqCreatedPerDist'],
                    $totals['totalLqCancelledPerDist'],
                    $totals['totalLqTransferedPerDist'],
                    $totals['totalLqFinishedPerDist'],

                    $totals['totalWdCreatedPerDist'],
                    $totals['totalWdCancelledPerDist'],
                    $totals['totalWdFinishedPerDist'],

                    self::STYLE_LIGHTGREY
                );
                $currentDistId = false;
                $totals['totalMosruCreatedPerDist'] = 0;
                $totals['totalMosruCancelledPerDist'] = 0;
                $totals['totalMosruTransferedPerDist'] = 0;
                $totals['totalMosruFinishedPerDist'] = 0;

                $totals['totalPhoneCreatedPerDist'] = 0;
                $totals['totalPhoneCancelledPerDist'] = 0;
                $totals['totalPhoneTransferedPerDist'] = 0;
                $totals['totalPhoneFinishedPerDist'] = 0;

                $totals['totalLqCreatedPerDist'] = 0;
                $totals['totalLqCancelledPerDist'] = 0;
                $totals['totalLqTransferedPerDist'] = 0;
                $totals['totalLqFinishedPerDist'] = 0;

                $totals['totalWdCreatedPerDist'] = 0;
                $totals['totalWdCancelledPerDist'] = 0;
                $totals['totalWdFinishedPerDist'] = 0;
            }

            // если данные в текущем округе кончились, выводим итог
            if($currentAreaId !== $report_row['id_area'] && $currentAreaId !== false) {
                $this->_renderTotalRow(
                    'Итого по округу',
                    $totals['totalMosruCreatedPerArea'],
                    $totals['totalMosruCancelledPerArea'],
                    $totals['totalMosruTransferedPerArea'],
                    $totals['totalMosruFinishedPerArea'],

                    $totals['totalPhoneCreatedPerArea'],
                    $totals['totalPhoneCancelledPerArea'],
                    $totals['totalPhoneTransferedPerArea'],
                    $totals['totalPhoneFinishedPerArea'],

                    $totals['totalLqCreatedPerArea'],
                    $totals['totalLqCancelledPerArea'],
                    $totals['totalLqTransferedPerArea'],
                    $totals['totalLqFinishedPerArea'],

                    $totals['totalWdCreatedPerArea'],
                    $totals['totalWdCancelledPerArea'],
                    $totals['totalWdFinishedPerArea'],

                    self::STYLE_LIGHTGREY
                );
                $currentDistId = false;
                $totals['totalMosruCreatedPerArea'] = 0;
                $totals['totalMosruCancelledPerArea'] = 0;
                $totals['totalMosruTransferedPerArea'] = 0;
                $totals['totalMosruFinishedPerArea'] = 0;

                $totals['totalPhoneCreatedPerArea'] = 0;
                $totals['totalPhoneCancelledPerArea'] = 0;
                $totals['totalPhoneTransferedPerArea'] = 0;
                $totals['totalPhoneFinishedPerArea'] = 0;

                $totals['totalLqCreatedPerArea'] = 0;
                $totals['totalLqCancelledPerArea'] = 0;
                $totals['totalLqTransferedPerArea'] = 0;
                $totals['totalLqFinishedPerArea'] = 0;

                $totals['totalWdCreatedPerArea'] = 0;
                $totals['totalWdCancelledPerArea'] = 0;
                $totals['totalWdFinishedPerArea'] = 0;
            }

            // если начался новый округ, выводим его название
            if($currentAreaId !== $report_row['id_area']) {
                $currentAreaId = $report_row['id_area'];
                $this->_renderHeaderRow(
                    $report_row['area_name'] ?? self::NO_AREA,
                    self::STYLE_MIDGREY
                );
            }

            // если начался новый район, выводим его название
            if($currentDistId !== $report_row['id_district']) {
                $currentDistId = $report_row['id_district'];
                $this->_renderHeaderRow(
                    $report_row['dist_name'] ?? self::NO_DISTRICT,
                    self::STYLE_LIGHTGREY
                );
            }

            $this->_renderRow($report_row['short_name'], $report_row);

            // ведем подсчет по району
            $totals['totalMosruCreatedPerDist'] += $report_row['mos_ru_created'];
            $totals['totalMosruCancelledPerDist'] += $report_row['mos_ru_cancelled'];
            $totals['totalMosruTransferedPerDist'] += $report_row['mos_ru_transfered'];
            $totals['totalMosruFinishedPerDist'] += $report_row['mos_ru_finished'];

            $totals['totalPhoneCreatedPerDist'] += $report_row['phone_created'];
            $totals['totalPhoneCancelledPerDist'] += $report_row['phone_cancelled'];
            $totals['totalPhoneTransferedPerDist'] += $report_row['phone_transfered'];
            $totals['totalPhoneFinishedPerDist'] += $report_row['phone_finished'];

            $totals['totalLqCreatedPerDist'] += $report_row['lq_created'];
            $totals['totalLqCancelledPerDist'] += $report_row['lq_cancelled'];
            $totals['totalLqTransferedPerDist'] += $report_row['lq_transfered'];
            $totals['totalLqFinishedPerDist'] += $report_row['lq_finished'];

            $totals['totalWdCreatedPerDist'] += $report_row['wd_created'];
            $totals['totalWdCancelledPerDist'] += $report_row['wd_cancelled'];
            $totals['totalWdFinishedPerDist'] += $report_row['wd_finished'];

            // ведем подсчет по округу
            $totals['totalMosruCreatedPerArea'] += $report_row['mos_ru_created'];
            $totals['totalMosruCancelledPerArea'] += $report_row['mos_ru_cancelled'];
            $totals['totalMosruTransferedPerArea'] += $report_row['mos_ru_transfered'];
            $totals['totalMosruFinishedPerArea'] += $report_row['mos_ru_finished'];

            $totals['totalPhoneCreatedPerArea'] += $report_row['phone_created'];
            $totals['totalPhoneCancelledPerArea'] += $report_row['phone_cancelled'];
            $totals['totalPhoneTransferedPerArea'] += $report_row['phone_transfered'];
            $totals['totalPhoneFinishedPerArea'] += $report_row['phone_finished'];

            $totals['totalLqCreatedPerArea'] += $report_row['lq_created'];
            $totals['totalLqCancelledPerArea'] += $report_row['lq_cancelled'];
            $totals['totalLqTransferedPerArea'] += $report_row['lq_transfered'];
            $totals['totalLqFinishedPerArea'] += $report_row['lq_finished'];

            $totals['totalWdCreatedPerArea'] += $report_row['wd_created'];
            $totals['totalWdCancelledPerArea'] += $report_row['wd_cancelled'];
            $totals['totalWdFinishedPerArea'] += $report_row['wd_finished'];

            // ведем общий подсчет
            $totals['totalMosruCreated'] += $report_row['mos_ru_created'];
            $totals['totalMosruCancelled'] += $report_row['mos_ru_cancelled'];
            $totals['totalMosruTransfered'] += $report_row['mos_ru_transfered'];
            $totals['totalMosruFinished'] += $report_row['mos_ru_finished'];

            $totals['totalPhoneCreated'] += $report_row['phone_created'];
            $totals['totalPhoneCancelled'] += $report_row['phone_cancelled'];
            $totals['totalPhoneTransfered'] += $report_row['phone_transfered'];
            $totals['totalPhoneFinished'] += $report_row['phone_finished'];

            $totals['totalLqCreated'] += $report_row['lq_created'];
            $totals['totalLqCancelled'] += $report_row['lq_cancelled'];
            $totals['totalLqTransfered'] += $report_row['lq_transfered'];
            $totals['totalLqFinished'] += $report_row['lq_finished'];

            $totals['totalWdCreated'] += $report_row['wd_created'];
            $totals['totalWdCancelled'] += $report_row['wd_cancelled'];
            $totals['totalWdFinished'] += $report_row['wd_finished'];
        }

        /*
         * Итог по последнему району
         */
        $this->_renderTotalRow(
            'Итого по району',
            $totals['totalMosruCreatedPerDist'],
            $totals['totalMosruCancelledPerDist'],
            $totals['totalMosruTransferedPerDist'],
            $totals['totalMosruFinishedPerDist'],

            $totals['totalPhoneCreatedPerDist'],
            $totals['totalPhoneCancelledPerDist'],
            $totals['totalPhoneTransferedPerDist'],
            $totals['totalPhoneFinishedPerDist'],

            $totals['totalLqCreatedPerDist'],
            $totals['totalLqCancelledPerDist'],
            $totals['totalLqTransferedPerDist'],
            $totals['totalLqFinishedPerDist'],

            $totals['totalWdCreatedPerDist'],
            $totals['totalWdCancelledPerDist'],
            $totals['totalWdFinishedPerDist'],
            self::STYLE_LIGHTGREY
        );

        /*
         * Итог по последнему округу
         */
        $this->_renderTotalRow(
            'Итого по округу',
            $totals['totalMosruCreatedPerArea'],
            $totals['totalMosruCancelledPerArea'],
            $totals['totalMosruTransferedPerArea'],
            $totals['totalMosruFinishedPerArea'],

            $totals['totalPhoneCreatedPerArea'],
            $totals['totalPhoneCancelledPerArea'],
            $totals['totalPhoneTransferedPerArea'],
            $totals['totalPhoneFinishedPerArea'],

            $totals['totalLqCreatedPerArea'],
            $totals['totalLqCancelledPerArea'],
            $totals['totalLqTransferedPerArea'],
            $totals['totalLqFinishedPerArea'],

            $totals['totalWdCreatedPerArea'],
            $totals['totalWdCancelledPerArea'],
            $totals['totalWdFinishedPerArea'],
            self::STYLE_LIGHTGREY
        );

        /*
         * ВСЕГО
         */
        $this->_renderTotalRow(
            'ВСЕГО',
            $totals['totalMosruCreated'],
            $totals['totalMosruCancelled'],
            $totals['totalMosruTransfered'],
            $totals['totalMosruFinished'],

            $totals['totalPhoneCreated'],
            $totals['totalPhoneCancelled'],
            $totals['totalPhoneTransfered'],
            $totals['totalPhoneFinished'],

            $totals['totalLqCreated'],
            $totals['totalLqCancelled'],
            $totals['totalLqTransfered'],
            $totals['totalLqFinished'],

            $totals['totalWdCreated'],
            $totals['totalWdCancelled'],
            $totals['totalWdFinished'],
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

        // коэффициент для автовысоты ячейки, в которой выводится длинное название организации.
        // Предположим, что стандартной высоты хватит на 20 символов, иначе увеличиваем
        $coef = intdiv(mb_strlen($title), 20) + 1;

        // проверка длины названия организации. если длина менее 20 символов - оставляем стандартную высоту; если более 40 - увеличиваем вдвое; 60 - втрое и т.д.
        if ($coef >= 1) {
            $sheet
                ->getRowDimension($this->currentRowNum)
                ->setRowHeight(15 * $coef);
        }

        $sheet->fromArray(
            [
                $title,
                $report_row['mos_ru_created'],
                $report_row['mos_ru_cancelled'],
                $report_row['mos_ru_transfered'],
                $report_row['mos_ru_finished'],

                $report_row['phone_created'],
                $report_row['phone_cancelled'],
                $report_row['phone_transfered'],
                $report_row['phone_finished'],

                $report_row['lq_created'],
                $report_row['lq_cancelled'],
                $report_row['lq_transfered'],
                $report_row['lq_finished'],

                $report_row['wd_created'],
                $report_row['wd_cancelled'],
                $report_row['wd_finished'],
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
     * @param $totalVar1-15
     * @param bool $style
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function _renderTotalRow($title, $totalVar1, $totalVar2, $totalVar3, $totalVar4, $totalVar5,
                                       $totalVar6, $totalVar7, $totalVar8, $totalVar9, $totalVar10,
                                       $totalVar11, $totalVar12, $totalVar13, $totalVar14, $totalVar15, $style = FALSE)
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
                $totalVar8,
                $totalVar9,
                $totalVar10,
                $totalVar11,
                $totalVar12,
                $totalVar13,
                $totalVar14,
                $totalVar15,
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

        $sheet->mergeCells('A' . $this->currentRowNum . ':P' . $this->currentRowNum);

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

        //первая строка
        $sheet
            ->mergeCells('A1:P1')
            ->getColumnDimension('A');

        $sheet
            ->getRowDimension('1')
            ->setRowHeight(30);

        $message = 'Общий отчет по приемам за период с ' . $from . ' по ' . $to;
        $sheet->setCellValueByColumnAndRow(
            '1',
            $this->currentRowNum,
            $message
        );

        //вторая строка
        $sheet
            ->mergeCells('A2:P2')
            ->getColumnDimension('A');

        $sheet
            ->setCellValue('A2', 'Обработано обращений (записей на прием)');

        //третья строка
        $sheet
            ->mergeCells('B3:E3')
            ->getColumnDimension('B');

        $sheet
            ->setCellValue('B3', 'mos.ru');

        $sheet
            ->mergeCells('F3:I3')
            ->getColumnDimension('F');

        $sheet
            ->setCellValue('F3', 'Телефон');

        $sheet
            ->mergeCells('J3:M3')
            ->getColumnDimension('J');

        $sheet
            ->setCellValue('J3', 'Живая очередь');

        $sheet
            ->mergeCells('N3:P3')
            ->getColumnDimension('N');

        $sheet
            ->setCellValue('N3', 'Направление');

        //четвертая строка
        $sheet
            ->getColumnDimension('A')
            ->setWidth(30);

        $sheet
            ->getColumnDimension('B')
            ->setWidth(9);

        $sheet
            ->setCellValue('B4', 'Создано');

        $sheet
            ->getColumnDimension('C')
            ->setWidth(10);

        $sheet
            ->setCellValue('C4', 'Отменено');

        $sheet
            ->getColumnDimension('D')
            ->setWidth(12);

        $sheet
            ->setCellValue('D4', 'Перенесено');

        $sheet
            ->getColumnDimension('E')
            ->setWidth(12);

        $sheet
            ->setCellValue('E4', 'Завершено');

        $sheet
            ->getColumnDimension('F')
            ->setWidth(9);

        $sheet
            ->setCellValue('F4', 'Создано');

        $sheet
            ->getColumnDimension('G')
            ->setWidth(10);

        $sheet
            ->setCellValue('G4', 'Отменено');

        $sheet
            ->getColumnDimension('H')
            ->setWidth(12);

        $sheet
            ->setCellValue('H4', 'Перенесено');

        $sheet
            ->getColumnDimension('I')
            ->setWidth(12);

        $sheet
            ->setCellValue('I4', 'Завершено');

        $sheet
            ->getColumnDimension('J')
            ->setWidth(9);

        $sheet
            ->setCellValue('J4', 'Создано');

        $sheet
            ->getColumnDimension('K')
            ->setWidth(10);

        $sheet
            ->setCellValue('K4', 'Отменено');

        $sheet
            ->getColumnDimension('L')
            ->setWidth(12);

        $sheet
            ->setCellValue('L4', 'Перенесено');

        $sheet
            ->getColumnDimension('M')
            ->setWidth(12);

        $sheet
            ->setCellValue('M4', 'Завершено');

        $sheet
            ->getColumnDimension('N')
            ->setWidth(9);

        $sheet
            ->setCellValue('N4', 'Создано');

        $sheet
            ->getColumnDimension('O')
            ->setWidth(10);

        $sheet
            ->setCellValue('O4', 'Отменено');

        $sheet
            ->getColumnDimension('P')
            ->setWidth(12);

        $sheet
            ->setCellValue('P4', 'Завершено');

        $sheet
            ->getStyle('A2:P4')
            ->getAlignment()->setWrapText(true); // Автоперенос

        $sheet
            ->getStyle('A1:P4')
            ->applyFromArray(self::STYLE_HEADER);

        $sheet
            ->getStyle('A:A')
            ->getAlignment()->setWrapText(true); // Автоперенос для длинных организаций

        // Передвигаем свой указатель на строку ниже
        $this->currentRowNum = 5;
    }
}