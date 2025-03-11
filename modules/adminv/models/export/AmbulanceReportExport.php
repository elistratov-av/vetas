<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 18.07.19
 * Time: 13:50
 */

namespace app\modules\adminv\models\export;


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class AmbulanceReportExport
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

    const STYLE_HEADER = [
        'font' => [
            'bold' => true,
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
    ];

    const COL_COUNT = 9;

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
        @ini_set('memory_limit', '512M');

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

        $totals = [
            'total_calls' => 0,
            'total_cancelled' => 0,
            'cancelled_by_owner' => 0,
            'cancelled_by_org' => 0,
            'total_house_calls' => 0,
            'total_commercial' => 0,
            'total_free_for_blind' => 0,
            'total_free_for_the_rest' => 0
        ];

        foreach ($this->report as $report_key => $report_row) { // идем поэлементно по массиву с результатами запроса

            $this->_renderRow($report_row['spec_name'], $report_row);

            // ведем общий подсчет
            $totals['total_calls'] += $report_row['total_calls'];
            $totals['total_cancelled'] += $report_row['total_cancelled'];
            $totals['cancelled_by_owner'] += $report_row['cancelled_by_owner'];
            $totals['cancelled_by_org'] += $report_row['cancelled_by_org'];
            $totals['total_house_calls'] += $report_row['total_house_calls'];
            $totals['total_commercial'] += $report_row['total_commercial'];
            $totals['total_free_for_blind'] += $report_row['total_free_for_blind'];
            $totals['total_free_for_the_rest'] += $report_row['total_free_for_the_rest'];
        }

        /*
         * ВСЕГО
         */
        $this->_renderTotalRow(
            'ВСЕГО',
            $totals,
            self::STYLE_LIGHTGREY
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

        foreach ($report_row as $key => $value) {
            $value = trim($value);
            $value = ltrim($value, '=-+^');
            $report_row[$key] = $value;
        }

        $sheet->fromArray(
            [
                $title,
                $report_row['total_calls'],
                $report_row['total_cancelled'],
                $report_row['cancelled_by_owner'],
                $report_row['cancelled_by_org'],
                $report_row['total_house_calls'],
                $report_row['total_commercial'],
                $report_row['total_free_for_blind'],
                $report_row['total_free_for_the_rest'],
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
     * @param $totals
     * @param bool $style
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function _renderTotalRow($title, $totals, $style = FALSE)
    {
        $sheet = $this->spreadsheet->getActiveSheet();

        $sheet->fromArray(
            [
                $title,
                $totals['total_calls'],
                $totals['total_cancelled'],
                $totals['cancelled_by_owner'],
                $totals['cancelled_by_org'],
                $totals['total_house_calls'],
                $totals['total_commercial'],
                $totals['total_free_for_blind'],
                $totals['total_free_for_the_rest'],
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
            ->mergeCells('A1:I1')
            ->getColumnDimension('A')
            ->setAutoSize(true); // Авто-ширина

        $sheet
            ->getRowDimension('1')
            ->setRowHeight(30);

        $message = 'Отчет по машинам "ветеринарной помощи на дому" (диспетчерская) с ' . $from . ' по ' . $to;
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
            ->getColumnDimension('I')
            ->setWidth(15);

        $sheet
            ->getRowDimension('2')
            ->setRowHeight(55); // Высота

        $sheet
            ->getStyle('A2:I2')
            ->getAlignment()->setWrapText(true); // Автоперенос

        $sheet
            ->getStyle('A1:I2')
            ->applyFromArray(self::STYLE_HEADER);

        $sheet
            ->getStyle('A:A')
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_TEXT);

        $sheet
            ->setCellValue('A2', 'Специалист');

        $sheet
            ->setCellValue('B2', 'Количество принятых звонков');

        $sheet
            ->setCellValue('C2', 'Общее количество отказов');

        $sheet
            ->setCellValue('D2', 'Количество отказов владельцем');

        $sheet
            ->setCellValue('E2', 'Количество отказов специалистом');

        $sheet
            ->setCellValue('F2', 'Количество выездов');

        $sheet
            ->setCellValue('G2', 'Количество платных выездов');

        $sheet
            ->setCellValue('H2', 'Количество льготных выездов (незрячие)');

        $sheet
            ->setCellValue('I2', 'Количество льготных выездов (все остальные)');

        // Передвигаем свой указатель на строку ниже
        $this->currentRowNum = 3;
    }
}
