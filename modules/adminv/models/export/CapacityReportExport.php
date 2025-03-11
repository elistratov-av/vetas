<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 12.07.19
 * Time: 13:39
 */

namespace app\modules\adminv\models\export;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

/**
 * Class CapacityReportExport
 * @package app\modules\adminv\models\export
 */
class CapacityReportExport
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

    /**
     *
     */
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
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
    ];

    const COL_COUNT = 2;


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

        $currentOrgId = null;
        $totalPerOrg = 0;
        $total = 0;

        foreach ($this->report as $report_row) { // идем поэлементно по массиву с результатами запроса
//            если данные в текущей орагнизации кончились, выводим итог
            if($currentOrgId != $report_row['id_organization'] && $currentOrgId != null) {
                $this->_renderTotalRow(
                'Итого по организации',
                $totalPerOrg,
                self::STYLE_LIGHTGREY
                );
                $currentOrgId = null;
                $totalPerOrg = 0;
            }

//            если началась новая организация, выводим ее название
            if($currentOrgId != $report_row['id_organization'] || $currentOrgId == null) {
                $currentOrgId = $report_row['id_organization'];
                $this->_renderHeaderRow(
                $report_row['short_name'],
                self::STYLE_LIGHTGREY
                );
            }

            $this->_renderRow($report_row['name'], $report_row);

            // ведем подсчет по организации
            $totalPerOrg += $report_row['sum'];
            // ведем общий подсчет
            $total += $report_row['sum'];
        }

        /*
         * Итог по последней организации
         */
        $this->_renderTotalRow(
            'Итого по организации',
            $totalPerOrg,
            self::STYLE_LIGHTGREY
        );

        /*
         * ВСЕГО
         */
        $this->_renderTotalRow(
            'ВСЕГО',
            $total,
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

        foreach ($report_row as $key => $value) {
            $value = trim($value);
            $value = ltrim($value, '=-+^');
            $report_row[$key] = $value;
        }

        $sheet->fromArray(
            [
                $title,
                $report_row['sum'] ,
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
     * Выводит строку с итогом по
     * @param $title
     * @param $totalVar
     * @param bool $style
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function _renderTotalRow($title, $totalVar, $style = FALSE)
    {
        $sheet = $this->spreadsheet->getActiveSheet();

        $sheet->fromArray(
            [
                $title,
                $totalVar,
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
            ->mergeCells('A1:B1')
            ->getColumnDimension('A')
            ->setAutoSize(true); // Авто-ширина

        $sheet
            ->getStyle('A1:B1')
            ->getAlignment()->setWrapText(true); // Автоперенос

        $sheet
            ->getRowDimension('1')
            ->setRowHeight(30);

        $message = 'Отчет о загрузке мощностей за период с ' . $from . ' по ' . $to;
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
            ->getRowDimension('2')
            ->setRowHeight(30); // Высота

        $sheet
            ->getStyle('A2:B2')
            ->getAlignment()->setWrapText(true); // Автоперенос

        $sheet
            ->getStyle('A1:B2')
            ->applyFromArray(self::STYLE_HEADER);

        $sheet
            ->getStyle('A:A')
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_TEXT)
        ;

        $sheet
            ->setCellValue('A2', 'Оборудование на балансе');

        $sheet
            ->setCellValue('B2', 'Использовано раз');

        // Передвигаем свой указатель на строку ниже
        $this->currentRowNum = 3;
    }
}
