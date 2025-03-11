<?php

namespace app\modules\adminv\models\export;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class VaccinationReportExport
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
     * @var array Итог
     */
    protected $totalCount;

    /**
     * @var int Номер текущей строки
     */
    protected $currentRowNum = 1;

    /**
     * @var array Промежуточный итог по округу
     */
    protected $totalCountArea;

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
        ],
    ];

    const COL_COUNT = 13;
    const NO_AREA = 'Округ не найден';

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

        $this->sendXlsx($this->spreadsheet, $filename);
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
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     *
     * Выводит основное тело отчета
     *
     */
    protected function renderBody()
    {
        $currentAreaId = null;
        $totalCatsPerArea = 0;
        $totalCatsRabicanPerArea = 0;
        $totalCatsComplexPerArea = 0;
        $totalCatsRejectedPerArea = 0;
        $totalDogsPerArea = 0;
        $totalDogsRabicanPerArea = 0;
        $totalDogsComplexPerArea = 0;
        $totalDogsRejectedPerArea = 0;
        $totalOtherPerArea = 0;
        $totalOtherRabicanPerArea = 0;
        $totalOtherComplexPerArea = 0;
        $totalOtherRejectedPerArea = 0;

        $totalCats = 0;
        $totalCatsRabican = 0;
        $totalCatsComplex = 0;
        $totalCatsRejected = 0;
        $totalDogs = 0;
        $totalDogsRabican = 0;
        $totalDogsComplex = 0;
        $totalDogsRejected = 0;
        $totalOther = 0;
        $totalOtherRabican = 0;
        $totalOtherComplex = 0;
        $totalOtherRejected = 0;

        foreach($this->report as $report_row) {

            if ($currentAreaId != $report_row['id_area'] && $currentAreaId != null) {

                $this->_renderTotalRow(
                    'Итого по округу',
                    $totalCatsPerArea,
                    $totalCatsRabicanPerArea,
                    $totalCatsComplexPerArea,
                    $totalCatsRejectedPerArea,
                    $totalDogsPerArea,
                    $totalDogsRabicanPerArea,
                    $totalDogsComplexPerArea,
                    $totalDogsRejectedPerArea,
                    $totalOtherPerArea,
                    $totalOtherRabicanPerArea,
                    $totalOtherComplexPerArea,
                    $totalOtherRejectedPerArea,
                    self::STYLE_LIGHTGREY
                );

                $totalCatsPerArea = 0;
                $totalCatsRabicanPerArea = 0;
                $totalCatsComplexPerArea = 0;
                $totalCatsRejectedPerArea = 0;
                $totalDogsPerArea = 0;
                $totalDogsRabicanPerArea = 0;
                $totalDogsComplexPerArea = 0;
                $totalDogsRejectedPerArea = 0;
                $totalOtherPerArea = 0;
                $totalOtherRabicanPerArea = 0;
                $totalOtherComplexPerArea = 0;
                $totalOtherRejectedPerArea = 0;
            }
            if ($currentAreaId != $report_row['id_area']) {
                $currentAreaId = $report_row['id_area'];
                $this->_renderHeaderRow(
                    $report_row['name'] ?? self::NO_AREA,
                    self::STYLE_LIGHTGREY
                );
            }

            $this->_renderRow($report_row['short_name'] ?? 'Организация не найдена', $report_row);

            $totalCatsPerArea += $report_row['total_cats'];
            $totalCatsRabicanPerArea += $report_row['total_rabies_cats'];
            $totalCatsComplexPerArea += $report_row['total_complex_cats'];
            $totalCatsRejectedPerArea += $report_row['total_reject_cats'];
            $totalDogsPerArea += $report_row['total_dogs'];
            $totalDogsRabicanPerArea += $report_row['total_rabies_dogs'];
            $totalDogsComplexPerArea += $report_row['total_complex_dogs'];
            $totalDogsRejectedPerArea += $report_row['total_reject_dogs'];
            $totalOtherPerArea += $report_row['total_other'];
            $totalOtherRabicanPerArea += $report_row['total_rabies_other'];
            $totalOtherComplexPerArea += $report_row['total_complex_other'];
            $totalOtherRejectedPerArea += $report_row['total_reject_other'];

            $totalCats += $report_row['total_cats'];
            $totalCatsRabican += $report_row['total_rabies_cats'];
            $totalCatsComplex += $report_row['total_complex_cats'];
            $totalCatsRejected += $report_row['total_reject_cats'];
            $totalDogs += $report_row['total_dogs'];
            $totalDogsRabican += $report_row['total_rabies_dogs'];
            $totalDogsComplex += $report_row['total_complex_dogs'];
            $totalDogsRejected += $report_row['total_reject_dogs'];
            $totalOther += $report_row['total_other'];
            $totalOtherRabican += $report_row['total_rabies_other'];
            $totalOtherComplex += $report_row['total_complex_other'];
            $totalOtherRejected += $report_row['total_reject_other'];
        }

        /*
         * Итог по последнему округу
         */
        $this->_renderTotalRow(
            'Итого по округу',
            $totalCatsPerArea,
            $totalCatsRabicanPerArea,
            $totalCatsComplexPerArea,
            $totalCatsRejectedPerArea,
            $totalDogsPerArea,
            $totalDogsRabicanPerArea,
            $totalDogsComplexPerArea,
            $totalDogsRejectedPerArea,
            $totalOtherPerArea,
            $totalOtherRabicanPerArea,
            $totalOtherComplexPerArea,
            $totalOtherRejectedPerArea,
            self::STYLE_LIGHTGREY
        );

        /*
         * ВСЕГО
         */
        $this->_renderTotalRow(
            'ВСЕГО',
            $totalCats,
            $totalCatsRabican,
            $totalCatsComplex,
            $totalCatsRejected,
            $totalDogs,
            $totalDogsRabican,
            $totalDogsComplex,
            $totalDogsRejected,
            $totalOther,
            $totalOtherRabican,
            $totalOtherComplex,
            $totalOtherRejected,
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

        // коэффициент для автовысоты ячейки, в которой выводится название организации.
        // Предположим, что стандартной высоты хватит на 35 символов, иначе увеличиваем
        $coef = intdiv(mb_strlen($title), 35) + 1;

        // проверка длины названия услуги. если длина менее 35 символов - оставляем стандартную высоту; если более 70 - увеличиваем вдвое; 105 - втрое и т.д.
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
                $report_row['total_cats'],
                $report_row['total_rabies_cats'],
                $report_row['total_complex_cats'],
                $report_row['total_reject_cats'],
                $report_row['total_dogs'],
                $report_row['total_rabies_dogs'],
                $report_row['total_complex_dogs'],
                $report_row['total_reject_dogs'],
                $report_row['total_other'],
                $report_row['total_rabies_other'],
                $report_row['total_complex_other'],
                $report_row['total_reject_other'],
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
     * @param $totalVar1-12
     * @param bool $style
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function _renderTotalRow($title, $totalVar1, $totalVar2, $totalVar3, $totalVar4, $totalVar5,
                                       $totalVar6, $totalVar7, $totalVar8, $totalVar9, $totalVar10, $totalVar11, $totalVar12, $style = FALSE)
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

        $sheet->mergeCells('A' . $this->currentRowNum . ':M' . $this->currentRowNum);

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
     *
     * Формирует шапку
     *
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
            ->mergeCells('A1:M1')
            ->getColumnDimension('A');

        $sheet
            ->getRowDimension('1')
            ->setRowHeight(30);

        $message = 'Отчет по охвату вакцинацией против бешенства с ' . $from . ' по ' . $to;
        $sheet->setCellValueByColumnAndRow(
            '1',
            $this->currentRowNum,
            $message
        );

        $sheet
            ->getStyle('A:A')
            ->getAlignment()->setWrapText(true); // Автоперенос

        $sheet
            ->getColumnDimension('A')
            ->setWidth(40); // Авто-ширина

        $sheet
            ->getRowDimension('3')
            ->setRowHeight(55); // Высота

        $sheet
            ->getStyle('A1:M3')
            ->getAlignment()->setWrapText(true); // Автоперенос

        $sheet
            ->getStyle('A1:M3')
            ->applyFromArray(self::STYLE_HEADER);

        $sheet
            ->getStyle('A:A')
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_TEXT)
        ;

        $sheet
            ->getColumnDimension('B')
            ->setWidth(12);

        $sheet
            ->getColumnDimension('C')
            ->setWidth(12);

        $sheet
            ->getColumnDimension('D')
            ->setWidth(14);

        $sheet
            ->getColumnDimension('E')
            ->setWidth(13);

        $sheet
            ->getColumnDimension('F')
            ->setWidth(12);

        $sheet
            ->getColumnDimension('G')
            ->setWidth(12);

        $sheet
            ->getColumnDimension('H')
            ->setWidth(14);

        $sheet
            ->getColumnDimension('I')
            ->setWidth(13);

        $sheet
            ->getColumnDimension('J')
            ->setWidth(12);

        $sheet
            ->getColumnDimension('K')
            ->setWidth(12);

        $sheet
            ->getColumnDimension('L')
            ->setWidth(14);

        $sheet
            ->getColumnDimension('M')
            ->setWidth(13);

        /*
         * Организация
         */
        $sheet->mergeCells('A2:A3')
            ->setCellValue('A2', 'Организация');

        /*
         * Кошки
         */
        $sheet
            ->mergeCells('B2:B3')
            ->setCellValue('B2', 'Кошек на учете')

            ->mergeCells('C2:D2')
            ->setCellValue('C2', 'Вакцинировано')
            ->setCellValue('C3', 'Вакциной Рабикан')
            ->setCellValue('D3', 'Комплексной вакциной')


            ->mergeCells('E2:E3')
            ->setCellValue('E2', 'Отказ от вакцинации')
        ;

        /*
         * Собаки
         */
        $sheet
            ->mergeCells('F2:F3')
            ->setCellValue('F2', 'Собак на учете')

            ->mergeCells('G2:H2')
            ->setCellValue('G2', 'Вакцинировано')
            ->setCellValue('G3', 'Вакциной Рабикан')
            ->setCellValue('H3', 'Комплексной вакциной')


            ->mergeCells('I2:I3')
            ->setCellValue('I2', 'Отказ от вакцинации')
        ;

        /*
         *  Прочих животных на учете
         */
        $sheet
            ->mergeCells('J2:J3')
            ->setCellValue('J2', 'Прочих животных на учете')

            ->mergeCells('K2:L2')
            ->setCellValue('K2', 'Вакцинировано')
            ->setCellValue('K3', 'Вакциной Рабикан')
            ->setCellValue('L3', 'Комплексной вакциной')


            ->mergeCells('M2:M3')
            ->setCellValue('M2', 'Отказ от вакцинации')
        ;

        // Передвигаем свой указатель на строку ниже
        $this->currentRowNum = 4;
    }
}
