<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 03.09.20
 * Time: 15:15
 */

namespace app\modules\adminv\models\export;


use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class EmailReportExport
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

    const STYLE_AREATEXT = [
        'font' => [
            'bold' => true,
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => [
                'argb' => 'b5dcf2',
            ],
        ]
    ];

    const STYLE_AREANUMBER = [
        'font' => [
            'bold' => true,
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => [
                'argb' => 'b5dcf2',
            ],
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
    ];

    const STYLE_TOTALTEXT = [
        'font' => [
            'bold' => true,
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => [
                'argb' => '9ac9e2',
            ],
        ]
    ];

    const STYLE_TOTALNUMBER = [
        'font' => [
            'bold' => true,
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => [
                'argb' => '9ac9e2',
            ],
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
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

    const STYLE_ROW = [
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
    ];



    const NO_AREA = 'Округ не найден';
    const NO_DISTRICT = 'Район не найден';

    const COL_COUNT = 10;


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

        $currentAreaId = null;
        $count = 1; //просто счетчик для первого столбца
        $totals = [
            'totalOwnersPerArea' => 0,
            'totalOwners' => 0,
            'totalEmailPerArea' => 0,
            'totalEmail' => 0,
            'totalConfirmedPerArea' => 0,
            'totalConfirmed' => 0,
            'totalUnconfirmedPerArea' => 0,
            'totalUnconfirmed' => 0,
            'totalRegMosruPerArea' => 0,
            'totalRegMosru' => 0,
            'totalEmailSpkPerArea' => 0,
            'totalEmailSpk' => 0,
            'totalPushSpkPerArea' => 0,
            'totalPushSpk' => 0,
        ];

        foreach ($this->report as $report_row) { // идем поэлементно по массиву с результатами запроса
            // если данные в текущем округе кончились, выводим итог
            if($currentAreaId !== $report_row['id_area'] && $currentAreaId !== null) {

                $this->_renderTotalRow(
                    'Итого по округу',
                    "",
                    "",
                    $totals['totalOwnersPerArea'],
                    $totals['totalEmailPerArea'],
                    $totals['totalConfirmedPerArea'],
                    $totals['totalUnconfirmedPerArea'],
                    $totals['totalRegMosruPerArea'],
                    $totals['totalEmailSpkPerArea'],
                    $totals['totalPushSpkPerArea'],
                    self::STYLE_AREATEXT,
                    self::STYLE_AREANUMBER
                );
                $currentAreaId = null;
                $totals['totalOwnersPerArea'] = 0;
                $totals['totalEmailPerArea'] = 0;
                $totals['totalConfirmedPerArea'] = 0;
                $totals['totalUnconfirmedPerArea'] = 0;
                $totals['totalRegMosruPerArea'] = 0;
                $totals['totalEmailSpkPerArea'] = 0;
                $totals['totalPushSpkPerArea'] = 0;
            }

            // новый округ
            if($currentAreaId !== $report_row['id_area']) {
                $currentAreaId = $report_row['id_area'];
            }

            $this->_renderRow($count, $report_row, self::STYLE_ROW);

            // ведем подсчет
            $totals['totalOwnersPerArea'] += $report_row['total_owners'];
            $totals['totalEmailPerArea'] += $report_row['total_email'];
            $totals['totalConfirmedPerArea'] += $report_row['total_confirmed'];
            $totals['totalUnconfirmedPerArea'] += $report_row['total_unconfirmed'];
            $totals['totalRegMosruPerArea'] += $report_row['total_reg_mosru'];
            $totals['totalEmailSpkPerArea'] += $report_row['total_email_spk'];
            $totals['totalPushSpkPerArea'] += $report_row['total_push_spk'];
            $totals['totalOwners'] += $report_row['total_owners'];
            $totals['totalEmail'] += $report_row['total_email'];
            $totals['totalConfirmed'] += $report_row['total_confirmed'];
            $totals['totalUnconfirmed'] += $report_row['total_unconfirmed'];
            $totals['totalRegMosru'] += $report_row['total_reg_mosru'];
            $totals['totalEmailSpk'] += $report_row['total_email_spk'];
            $totals['totalPushSpk'] += $report_row['total_push_spk'];
            $count++;
        }

        /*
         * Итог по последнему округу
         */
        $this->_renderTotalRow(
            'Итого по округу',
            "",
            "",
            $totals['totalOwnersPerArea'],
            $totals['totalEmailPerArea'],
            $totals['totalConfirmedPerArea'],
            $totals['totalUnconfirmedPerArea'],
            $totals['totalRegMosruPerArea'],
            $totals['totalEmailSpkPerArea'],
            $totals['totalPushSpkPerArea'],
            self::STYLE_AREATEXT,
            self::STYLE_AREANUMBER
        );

        /*
         * ВСЕГО
         */
        $this->_renderTotalRow(
            'Всего',
            "",
            "",
            $totals['totalOwners'],
            $totals['totalEmail'],
            $totals['totalConfirmed'],
            $totals['totalUnconfirmed'],
            $totals['totalRegMosru'],
            $totals['totalEmailSpk'],
            $totals['totalPushSpk'],
            self::STYLE_TOTALTEXT,
            self::STYLE_TOTALNUMBER
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
        // Предположим, что стандартной высоты хватит на 14 символов, иначе увеличиваем
        $coef = intdiv(mb_strlen($report_row['area_name']), 14) + 1;

        // проверка длины названия АО. если длина менее 14 символов - оставляем стандартную высоту;
        // если более 28 - увеличиваем вдвое; 42 - втрое и т.д.
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

        $area = !empty($report_row['area_name']) ? $report_row['area_name'] : self::NO_AREA;
        $dist = !empty($report_row['dist_name']) ? $report_row['dist_name'] : self::NO_DISTRICT;

        $sheet->fromArray(
            [
                $title,
                $area,
                $dist,
                $report_row['total_owners'],
                $report_row['total_email'],
                $report_row['total_confirmed'],
                $report_row['total_unconfirmed'],
                $report_row['total_reg_mosru'],
                $report_row['total_email_spk'],
                $report_row['total_push_spk'],
            ],
            NULL,
            'A' . $this->currentRowNum,
            true
        );

        // Стиль?
        if (!empty($style)){
            $sheet->getStyleByColumnAndRow(
                4,
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
     * @param $totalVar1-9
     * @param bool $styleText
     * @param bool $styleNumber
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function _renderTotalRow($title, $totalVar1, $totalVar2, $totalVar3, $totalVar4, $totalVar5,
                                       $totalVar6, $totalVar7, $totalVar8, $totalVar9, $styleText = FALSE, $styleNumber = FALSE)
    {
        $sheet = $this->spreadsheet->getActiveSheet();
        $sheet->mergeCells('A' . $this->currentRowNum . ':C' . $this->currentRowNum);

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
            ],
            NULL,
            'A' . $this->currentRowNum,
            true
        );

        // Стиль?
        if (!empty($styleText)){
            $sheet->getStyleByColumnAndRow(
                1,
                $this->currentRowNum,
                3,
                $this->currentRowNum
            )
                ->applyFromArray($styleText);
        }

        if (!empty($styleNumber)){
            $sheet->getStyleByColumnAndRow(
                4,
                $this->currentRowNum,
                self::COL_COUNT,
                $this->currentRowNum
            )
                ->applyFromArray($styleNumber);
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

        $sheet->mergeCells('A' . $this->currentRowNum . ':J' . $this->currentRowNum);

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
            ->mergeCells('A1:J1')
            ->getColumnDimension('A');

        $sheet
            ->getRowDimension('1')
            ->setRowHeight(30);

        $message = 'Отчет по владельцам с электронной почтой за период с ' . $from . ' по ' . $to;
        $sheet->setCellValueByColumnAndRow(
            '1',
            $this->currentRowNum,
            $message
        );

        $sheet
            ->getRowDimension('2')
            ->setRowHeight(70);

        $sheet
            ->getColumnDimension('A')
            ->setWidth(7);

        $sheet
            ->setCellValue('A2', '№ п\п');

        $sheet
            ->getColumnDimension('B')
            ->setWidth(20);

        $sheet
            ->setCellValue('B2', 'Административный округ');

        $sheet
            ->getColumnDimension('C')
            ->setWidth(20);

        $sheet
            ->setCellValue('C2', 'Район');

        $sheet
            ->getColumnDimension('D')
            ->setWidth(20);

        $sheet
            ->setCellValue('D2', 'Общее количество владельцев животных');

        $sheet
            ->getColumnDimension('E')
            ->setWidth(20);

        $sheet
            ->setCellValue('E2', 'Количество владельцев с электронной почтой');

        $sheet
            ->getColumnDimension('F')
            ->setWidth(22);

        $sheet
            ->setCellValue('F2', 'Количество владельцев с подтверждёнными телефонными номерами');

        $sheet
            ->getColumnDimension('G')
            ->setWidth(22);

        $sheet
            ->setCellValue('G2', 'Количество владельцев с неподтверждёнными телефонными номерами');

        $sheet
            ->getColumnDimension('H')
            ->setWidth(22);

        $sheet
            ->setCellValue('H2', 'Количество владельцев, зарегистрированных на портале mos.ru');

        $sheet
            ->getColumnDimension('I')
            ->setWidth(18);

        $sheet
            ->setCellValue('I2', 'Количество владельцев, подписанных на E-mail рассылку');

        $sheet
            ->getColumnDimension('J')
            ->setWidth(18);

        $sheet
            ->setCellValue('J2', 'Количество владельцев, подписанных на push и ЛК');

        $sheet
            ->getStyle('A2:J200')
            ->getAlignment()->setWrapText(true); // Автоперенос

        $sheet
            ->getStyle('A1:J2')
            ->applyFromArray(self::STYLE_HEADER);

        // Передвигаем свой указатель на строку ниже
        $this->currentRowNum = 3;
    }

}