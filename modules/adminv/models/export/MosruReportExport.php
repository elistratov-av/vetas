<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 07.08.19
 * Time: 12:18
 */

namespace app\modules\adminv\models\export;

use app\modules\admin\models\Organization;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class MosruReportExport
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
     * @var array
     */
    protected $organizations;

    /**
     * @var int Номер текущей строки
     */
    protected $currentRowNum = 1;

    const STYLE_HEADER = [
        'font' => [
            'bold' => true,
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
    ];

    const COL_COUNT = 11;

    /**
     * @param      $report
     * @param null $filename
     * @param      $from
     * @param      $to
     * @param      $organizations
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\web\RangeNotSatisfiableHttpException
     */
    public function export($report, $filename = null, $from, $to, $organizations)
    {
        @ini_set('memory_limit', '1024M');

        $this->spreadsheet = new Spreadsheet();
        $this->report = $report;
        $this->organizations = $organizations;

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
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function renderBody()
    {
        $organizations = $this->organizations;

        foreach ($this->report as $report_key => $report_row) { // идем поэлементно по массиву с результатами запроса
            $title = $organizations[$report_row['id_organization']] ?? 'Неизвестная организация #' . $report_row['id_organization'];
            $this->_renderRow($title, $report_row);
        }
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
            if (!is_string($value)) {
                continue;
            }
            $value = trim($value);
            $value = ltrim($value, '=-+^');
            $report_row[$key] = $value;
        }

        $sheet->fromArray(
            [
                $title,
                $report_row['total'],
                $report_row['moved'],
                $report_row['canceled_by_owner'],
                $report_row['canceled_by_org'],
                $report_row['cats_visits'],
                $report_row['dogs_visits'],
                $report_row['other_visits'],
                $report_row['cats_finished_visits'],
                $report_row['dogs_finished_visits'],
                $report_row['other_finished_visits'],
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
            ->mergeCells('A1:K1')
            ->getColumnDimension('A')
            ->setAutoSize(true); // Авто-ширина

        $sheet
            ->getRowDimension('1')
            ->setRowHeight(30);

        $message = 'Статистика mos.ru с ' . $from . ' по ' . $to;
        $sheet->setCellValueByColumnAndRow(
            '1',
            $this->currentRowNum,
            $message
        );

        $sheet
            ->getColumnDimension('A')
            ->setAutoSize(true); // Авто-ширина

        $sheet
            ->mergeCells('D2:E2')
            ->mergeCells('F2:H2')
            ->mergeCells('I2:K2');

        $sheet
            ->getColumnDimension('B')
            ->setWidth(7);

        $sheet
            ->getColumnDimension('C')
            ->setWidth(13);

        $sheet
            ->getColumnDimension('D')
            ->setWidth(13);

        $sheet
            ->getColumnDimension('E')
            ->setWidth(15);

        $sheet
            ->getColumnDimension('F')
            ->setWidth(10);

        $sheet
            ->getColumnDimension('G')
            ->setWidth(10);

        $sheet
            ->getColumnDimension('H')
            ->setWidth(10);

        $sheet
            ->getColumnDimension('I')
            ->setWidth(10);

        $sheet
            ->getColumnDimension('J')
            ->setWidth(10);

        $sheet
            ->getColumnDimension('K')
            ->setWidth(10);

        $sheet
            ->getStyle('A2:K3')
            ->getAlignment()->setWrapText(true); // Автоперенос

        $sheet
            ->getStyle('A1:K3')
            ->applyFromArray(self::STYLE_HEADER);

        $sheet
            ->getStyle('A:A')
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_TEXT);

        $sheet
            ->setCellValue('D2', 'Отменено')
            ->setCellValue('F2', 'Записи для')
            ->setCellValue('I2', 'Проведено приемов для');

        $sheet
            ->setCellValue('A3', 'Организация')
            ->setCellValue('B3', 'Всего')
            ->setCellValue('C3', 'Перенесено')
            ->setCellValue('D3', 'владельцем')
            ->setCellValue('E3', 'организацией')
            ->setCellValue('F3', 'кошек')
            ->setCellValue('G3', 'собак')
            ->setCellValue('H3', 'иные')
            ->setCellValue('I3', 'кошек')
            ->setCellValue('J3', 'собак')
            ->setCellValue('K3', 'иные');

        // Передвигаем свой указатель на строку ниже
        $this->currentRowNum = 4;
    }
}
