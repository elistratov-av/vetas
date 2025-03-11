<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 16.07.19
 * Time: 14:20
 */

namespace app\modules\adminv\models\export;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class VetServicesReportExport
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

    const STYLE_TYPEGREY = [
        'font' => [
            'bold' => true,
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => [
                'argb' => 'bdcfe6',
            ],
        ]
    ];

    const STYLE_SPECGREY = [
        'font' => [
            'bold' => true,
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => [
                'argb' => 'a2bde2',
            ],
        ]
    ];

    const STYLE_ORGGREY = [
        'font' => [
            'bold' => true,
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => [
                'argb' => '6892d2',
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
                'argb' => '3268bc',
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

    const COL_COUNT = 12;


    /**
     * @param array $report
     * @param string $filename
     * @param string $from
     * @param string $to
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\web\RangeNotSatisfiableHttpException
     */
    public function export($report, $filename = null, $from, $to)
    {
        @ini_set('memory_limit', '512M');

        $this->spreadsheet = new Spreadsheet();
        $this->report = $report;

        $this->renderReport($from, $to);

        $this->sendXlsx($this->spreadsheet,$filename);
    }

    /**
     * @param string $from
     * @param string $to
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

        $currentOrgId = false;
        $currentSpecId = false;
        $currentTypeId = false;
        $totals = [
            'totalPaidPerType' => 0,
            'totalPaidPerSpec' => 0,
            'totalPaidPerOrg' => 0,
            'totalPaid' => 0,
            'totalAmountPerType' => 0,
            'totalAmountPerSpec' => 0,
            'totalAmountPerOrg' => 0,
            'totalAmount' => 0,
            'totalRabiesPerType' => 0,
            'totalRabiesPerSpec' => 0,
            'totalRabiesPerOrg' => 0,
            'totalRabies' => 0,
            'totalFreePerType' => 0,
            'totalFreePerSpec' => 0,
            'totalFreePerOrg' => 0,
            'totalFree' => 0,
            'totalBlindPerType' => 0,
            'totalBlindPerSpec' => 0,
            'totalBlindPerOrg' => 0,
            'totalBlind' => 0,
            'totalVeteranPerType' => 0,
            'totalVeteranPerSpec' => 0,
            'totalVeteranPerOrg' => 0,
            'totalVeteran' => 0,
            'totalDisabledPerType' => 0,
            'totalDisabledPerSpec' => 0,
            'totalDisabledPerOrg' => 0,
            'totalDisabled' => 0,
            'totalF1PerType' => 0,
            'totalF1PerSpec' => 0,
            'totalF1PerOrg' => 0,
            'totalF1' => 0,
            'totalF4PerType' => 0,
            'totalF4PerSpec' => 0,
            'totalF4PerOrg' => 0,
            'totalF4' => 0,
            'totalTSPerType' => 0,
            'totalTSPerSpec' => 0,
            'totalTSPerOrg' => 0,
            'totalTS' => 0,
        ];

        foreach ($this->report as $report_row) { // идем поэлементно по массиву с результатами запроса
//            var_dump($report_row); die();
            // если данные в текущем типе услуг кончились, выводим итог
            if(($currentTypeId != $report_row['type_id']
                && $currentTypeId != false
                || (($currentSpecId != $report_row['id_spec']
                    || $currentOrgId != $report_row['id_organization']))
                && $currentTypeId != false)
            ) {
                $this->_renderTotalRow(
                    'Итого по типу ',
                    $totals['totalPaidPerType'],
                    $totals['totalAmountPerType'],
                    $totals['totalRabiesPerType'],
                    $totals['totalFreePerType'],
                    $totals['totalBlindPerType'],
                    $totals['totalVeteranPerType'],
                    $totals['totalDisabledPerType'],
                    $totals['totalF1PerType'],
                    $totals['totalF4PerType'],
                    $totals['totalTSPerType'],
                    self::STYLE_TYPEGREY
                );
                $currentTypeId = false;
                $totals['totalPaidPerType'] = 0;
                $totals['totalAmountPerType'] = 0;
                $totals['totalRabiesPerType'] = 0;
                $totals['totalFreePerType'] = 0;
                $totals['totalBlindPerType'] = 0;
                $totals['totalVeteranPerType'] = 0;
                $totals['totalDisabledPerType'] = 0;
                $totals['totalF1PerType'] = 0;
                $totals['totalF4PerType'] = 0;
                $totals['totalTSPerType'] = 0;
            }

            // если данные у спеца кончились, выводим итог
            if($currentSpecId != $report_row['id_spec']
                && $currentSpecId != false
                || (($currentOrgId != $report_row['id_organization']
                    && $currentTypeId != false))) {
                $this->_renderTotalRow(
                    'Итого по специалисту ',
                    $totals['totalPaidPerSpec'],
                    $totals['totalAmountPerSpec'],
                    $totals['totalRabiesPerSpec'],
                    $totals['totalFreePerSpec'],
                    $totals['totalBlindPerSpec'],
                    $totals['totalVeteranPerSpec'],
                    $totals['totalDisabledPerSpec'],
                    $totals['totalF1PerSpec'],
                    $totals['totalF4PerSpec'],
                    $totals['totalTSPerSpec'],
                    self::STYLE_SPECGREY
                );
                $currentSpecId = false;
                $totals['totalPaidPerSpec'] = 0;
                $totals['totalAmountPerSpec'] = 0;
                $totals['totalRabiesPerSpec'] = 0;
                $totals['totalFreePerSpec'] = 0;
                $totals['totalBlindPerSpec'] = 0;
                $totals['totalVeteranPerSpec'] = 0;
                $totals['totalDisabledPerSpec'] = 0;
                $totals['totalF1PerSpec'] = 0;
                $totals['totalF4PerSpec'] = 0;
                $totals['totalTSPerSpec'] = 0;
            }

            // если данные в текущей организации кончились, выводим итог
            if($currentOrgId != $report_row['id_organization']
                && $currentOrgId != false) {
                $this->_renderTotalRow(
                    'Итого по организации ',
                    $totals['totalPaidPerOrg'],
                    $totals['totalAmountPerOrg'],
                    $totals['totalRabiesPerOrg'],
                    $totals['totalFreePerOrg'],
                    $totals['totalBlindPerOrg'],
                    $totals['totalVeteranPerOrg'],
                    $totals['totalDisabledPerOrg'],
                    $totals['totalF1PerOrg'],
                    $totals['totalF4PerOrg'],
                    $totals['totalTSPerOrg'],
                    self::STYLE_ORGGREY
                );
                $this->currentOrgId = false;
                $this->currentTypeId = false;
                $totals['totalPaidPerOrg'] = 0;
                $totals['totalAmountPerOrg'] = 0;
                $totals['totalRabiesPerOrg'] = 0;
                $totals['totalFreePerOrg'] = 0;
                $totals['totalBlindPerOrg'] = 0;
                $totals['totalVeteranPerOrg'] = 0;
                $totals['totalDisabledPerOrg'] = 0;
                $totals['totalF1PerOrg'] = 0;
                $totals['totalF4PerOrg'] = 0;
                $totals['totalTSPerOrg'] = 0;
            }

            // если началась новая орагнизация, выводим ее название
            if($currentOrgId !== $report_row['id_organization']) {
                $currentOrgId = $report_row['id_organization'];
                $this->_renderHeaderRow(
                    $report_row['short_name'],
                    self::STYLE_ORGGREY
                );
            }

            // если начался новый спец, выводим имя
            if ($currentSpecId !== $report_row['id_spec']) {
                $currentSpecId = $report_row['id_spec'];
                $this->_renderHeaderRow(
                    $report_row['fullname'],
                    self::STYLE_SPECGREY
                );
            }

            // если начался новый тип услуг, выводим его название
            if($currentTypeId !== $report_row['type_id']) {
                $currentTypeId = $report_row['type_id'];
                $this->_renderHeaderRow(
                    $report_row['type_name'],
                    self::STYLE_TYPEGREY
                );
            }

            $this->_renderRow($report_row['name'], $report_row);

            $totals['totalPaidPerType'] += $report_row['total_paid'];
            $totals['totalAmountPerType'] += $report_row['total_amount'];
            $totals['totalRabiesPerType'] += $report_row['total_rabies'];
            $totals['totalFreePerType'] += $report_row['total_free'];
            $totals['totalBlindPerType'] += $report_row['total_blind'];
            $totals['totalVeteranPerType'] += $report_row['total_veteran'];
            $totals['totalDisabledPerType'] += $report_row['total_disabled'];
            $totals['totalF1PerType'] += $report_row['total_f1'];
            $totals['totalF4PerType'] += $report_row['total_f4'];
            $totals['totalTSPerType'] += $report_row['total_ts'];

            // ведем подсчет по спецу
            $totals['totalPaidPerSpec'] += $report_row['total_paid'];
            $totals['totalAmountPerSpec'] += $report_row['total_amount'];
            $totals['totalRabiesPerSpec'] += $report_row['total_rabies'];
            $totals['totalFreePerSpec'] += $report_row['total_free'];
            $totals['totalBlindPerSpec'] += $report_row['total_blind'];
            $totals['totalVeteranPerSpec'] += $report_row['total_veteran'];
            $totals['totalDisabledPerSpec'] += $report_row['total_disabled'];
            $totals['totalF1PerSpec'] += $report_row['total_f1'];
            $totals['totalF4PerSpec'] += $report_row['total_f4'];
            $totals['totalTSPerSpec'] += $report_row['total_ts'];

            // ведем подсчет по организации
            $totals['totalPaidPerOrg'] += $report_row['total_paid'];
            $totals['totalAmountPerOrg'] += $report_row['total_amount'];
            $totals['totalRabiesPerOrg'] += $report_row['total_rabies'];
            $totals['totalFreePerOrg'] += $report_row['total_free'];
            $totals['totalBlindPerOrg'] += $report_row['total_blind'];
            $totals['totalVeteranPerOrg'] += $report_row['total_veteran'];
            $totals['totalDisabledPerOrg'] += $report_row['total_disabled'];
            $totals['totalF1PerOrg'] += $report_row['total_f1'];
            $totals['totalF4PerOrg'] += $report_row['total_f4'];
            $totals['totalTSPerOrg'] += $report_row['total_ts'];

            // ведем общий подсчет
            $totals['totalPaid'] += $report_row['total_paid'];
            $totals['totalAmount'] += $report_row['total_amount'];
            $totals['totalRabies'] += $report_row['total_rabies'];
            $totals['totalFree'] += $report_row['total_free'];
            $totals['totalBlind'] += $report_row['total_blind'];
            $totals['totalVeteran'] += $report_row['total_veteran'];
            $totals['totalDisabled'] += $report_row['total_disabled'];
            $totals['totalF1'] += $report_row['total_f1'];
            $totals['totalF4'] += $report_row['total_f4'];
            $totals['totalTS'] += $report_row['total_ts'];
        }

        /*
         * Итог по последнему типу услуг
         */
        $this->_renderTotalRow(
            'Итого по типу ',
            $totals['totalPaidPerType'],
            $totals['totalAmountPerType'],
            $totals['totalRabiesPerType'],
            $totals['totalFreePerType'],
            $totals['totalBlindPerType'],
            $totals['totalVeteranPerType'],
            $totals['totalDisabledPerType'],
            $totals['totalF1PerType'],
            $totals['totalF4PerType'],
            $totals['totalTSPerType'],
            self::STYLE_TYPEGREY
        );

        /*
             * Итог по последнему спецу
             */
        $this->_renderTotalRow(
            'Итого по специалисту ',
            $totals['totalPaidPerSpec'],
            $totals['totalAmountPerSpec'],
            $totals['totalRabiesPerSpec'],
            $totals['totalFreePerSpec'],
            $totals['totalBlindPerSpec'],
            $totals['totalVeteranPerSpec'],
            $totals['totalDisabledPerSpec'],
            $totals['totalF1PerSpec'],
            $totals['totalF4PerSpec'],
            $totals['totalTSPerSpec'],
            self::STYLE_SPECGREY
        );

        /*
         * Итог по последней организации
         */
        $this->_renderTotalRow(
            'Итого по организации ',
            $totals['totalPaidPerOrg'],
            $totals['totalAmountPerOrg'],
            $totals['totalRabiesPerOrg'],
            $totals['totalFreePerOrg'],
            $totals['totalBlindPerOrg'],
            $totals['totalVeteranPerOrg'],
            $totals['totalDisabledPerOrg'],
            $totals['totalF1PerOrg'],
            $totals['totalF4PerOrg'],
            $totals['totalTSPerOrg'],
            self::STYLE_ORGGREY
        );

        /*
         * ВСЕГО
         */
        $this->_renderTotalRow(
            'ВСЕГО ',
            $totals['totalPaid'],
            $totals['totalAmount'],
            $totals['totalRabies'],
            $totals['totalFree'],
            $totals['totalBlind'],
            $totals['totalVeteran'],
            $totals['totalDisabled'],
            $totals['totalF1'],
            $totals['totalF4'],
            $totals['totalTS'],
            self::STYLE_TOTALGRAY
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

        // коэффициент для автовысоты ячейки, в которой выводится длинное название ячейки.
        // Предположим, что стандартной высоты хватит на 50 символов, иначе увеличиваем
        $coef = intdiv(mb_strlen($title), 50) + 1;

        // проверка длины названия услуги. если длина менее 50 символов - оставляем стандартную высоту; если более 100 - увеличиваем вдвое; 150 - втрое и т.д.
        if ($coef >= 1) {
            $sheet
                ->getRowDimension($this->currentRowNum)
                ->setRowHeight(15 * $coef);
        }

        foreach ($report_row as $key => $value) {
            $value = trim($value);
            $value = ltrim($value, '=-+^');
            $report_row[$key] = $value;
        }

        $sheet->fromArray(
            [
                $title,
                $report_row['total_paid'],
                $report_row['total_amount'],
                $report_row['total_rabies'],
                $report_row['total_free'],
                $report_row['total_blind'],
                $report_row['total_veteran'],
                $report_row['total_disabled'],
                $report_row['total_f1'],
                $report_row['total_f4'],
                $report_row['total_ts'],

            ],
            NULL,
            'A' . $this->currentRowNum,
            true
        );

        $sheet
            ->getRowDimension($this->currentRowNum)
            ->setOutlineLevel(1);

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
     * @param bool $style
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function _renderTotalRow($title, $totalVar1, $totalVar2, $totalVar3,
                                       $totalVar4, $totalVar5, $totalVar6, $totalVar7,
                                       $totalVar8, $totalVar9, $totalVar10, $style = FALSE)
    {
        $sheet = $this->spreadsheet->getActiveSheet();

        $sheet->fromArray(
            [
                $title,
                '',
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
            ->mergeCells('A1:L1')
            ->getColumnDimension('A');

        $sheet
            ->getStyle('A:L')
            ->getAlignment()->setWrapText(true); // Автоперенос

        $sheet
            ->getRowDimension('1')
            ->setRowHeight(30);

        $message = 'Отчет об оказании ветеринарных услуг c ' . $from . ' по ' . $to;
        $sheet->setCellValueByColumnAndRow(
            '1',
            $this->currentRowNum,
            $message
        );

        $sheet
            ->mergeCells('A2:A4')
            ->getColumnDimension('A');
        $sheet
            ->mergeCells('B2:B4')
            ->getColumnDimension('B');
        $sheet
            ->mergeCells('C2:C4')
            ->getColumnDimension('C');
        $sheet
            ->mergeCells('D2:D4')
            ->getColumnDimension('D');
        $sheet
            ->mergeCells('E2:E4')
            ->getColumnDimension('E');
        $sheet
            ->mergeCells('F2:F4')
            ->getColumnDimension('F');

//        $sheet
//            ->getColumnDimension('A')
//            ->setWidth(70);
//
//        $sheet
//            ->getColumnDimension('B')
//            ->setWidth(15);
//
//        $sheet
//            ->getColumnDimension('C')
//            ->setWidth(15);
//
//        $sheet
//            ->getColumnDimension('D')
//            ->setWidth(15);
//
//        $sheet
//            ->getRowDimension('2')
//            ->setRowHeight(30); // Высота
//
//        $sheet
//            ->getStyle('A2:D2')
//            ->getAlignment()->setWrapText(true); // Автоперенос
//
//        $sheet
//            ->getStyle('A1:D2')
//            ->applyFromArray(self::STYLE_HEADER);
//
//        $sheet
//            ->getStyle('A:A')
//            ->getNumberFormat()
//            ->setFormatCode(NumberFormat::FORMAT_TEXT)
//        ;
//
//        $sheet
//            ->setCellValue('A2', 'Услуга');
//
//        $sheet
//            ->setCellValue('B2', 'Оказано количество');
//
//        $sheet
//            ->setCellValue('C2', 'Стоимость за единицу');
//
//        $sheet
//            ->setCellValue('D2', 'Стоимость услуг (общая)');

        // Передвигаем свой указатель на строку ниже
        $this->currentRowNum = 5;
    }
}
