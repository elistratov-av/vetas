<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 19.07.19
 * Time: 14:04
 */

namespace app\modules\adminv\models\export;

use app\models\db\OrganizationsTree;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class BalanceReportExport
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

    const STYLE_LIGHT = [
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

    const STYLE_HEADER = [
        'font' => [
            'bold' => true,
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
    ];

    const COL_COUNT = 8;


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

        foreach ($this->report as $report_row) { // идем поэлементно по массиву с результатами запроса
            // если началась новая орагнизация, выводим ее название
            if($currentOrgId !== $report_row['id_organization']) {
                $currentOrgId = $report_row['id_organization'];
                $this->_renderHeaderRow(
                    $report_row['short_name'],
                    self::STYLE_LIGHT
                );
            }

            $this->_renderRow($report_row['name'], $report_row);

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

        // коэффициент для автовысоты ячейки, в которой выводится длинное название ячейки.
        // Предположим, что стандартной высоты хватит на 45 символов, иначе увеличиваем
        $coef = intdiv(mb_strlen($title), 45) + 1;

        // проверка длины названия тмц. если длина менее 45 символов - оставляем стандартную высоту;
        // если более 90 - увеличиваем вдвое; 135 - втрое и т.д.
        if ($coef >= 1) {
            $sheet
                ->getRowDimension($this->currentRowNum)
                ->setRowHeight(15 * $coef);
        }

        foreach ($report_row as $key => $value) {
            $value = trim($value);
            $value = ltrim($value, '=+^');
            $report_row[$key] = $value;
        }

        $sheet->fromArray(
            [
                $report_row['drug_name'],
                $report_row['name'],
                $report_row['sum'] + $report_row['balance_before'] ?? 0,
                $report_row['income'] ?? 0,
                $report_row['outcome'] ?? 0,
                $report_row['transfer'] ?? 0,
                $report_row['used'] ?? 0,
                $report_row['sum'] + $report_row['balance_after'] ?? 0,

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
            ->getRowDimension('1')
            ->setRowHeight(30);

        $sheet
            ->getStyle('A:A')
            ->getAlignment()->setWrapText(true); // Автоперенос

        $message = 'Отчет по использованию препаратов за период с ' . $from . ' по ' . $to;
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
            ->setWidth(7);

        $sheet
            ->getColumnDimension('C')
            ->setWidth(16);

        $sheet
            ->getColumnDimension('D')
            ->setWidth(16);

        $sheet
            ->getColumnDimension('E')
            ->setWidth(16);

        $sheet
            ->getColumnDimension('F')
            ->setWidth(16);

        $sheet
            ->getColumnDimension('G')
            ->setWidth(16);

        $sheet
            ->getColumnDimension('H')
            ->setWidth(16);

        $sheet
            ->getRowDimension('2')
            ->setRowHeight(40); // Высота

        $sheet
            ->getStyle('A2:H2')
            ->getAlignment()->setWrapText(true); // Автоперенос

        $sheet
            ->getStyle('A1:H2')
            ->applyFromArray(self::STYLE_HEADER);

        $sheet
            ->setCellValue('A2', 'Препарат (ТМЦ)');

        $sheet
            ->setCellValue('B2', 'Ед. изм.');

        $sheet
            ->setCellValue('C2', 'Остаток на начало периода');

        $sheet
            ->setCellValue('D2', 'Поступило');

        $sheet
            ->setCellValue('E2', 'Списано с баланса');

        $sheet
            ->setCellValue('F2', 'Передано в другие организации');

        $sheet
            ->setCellValue('G2', 'Использовано в рамках приемов');

        $sheet
            ->setCellValue('H2', 'Остаток на конец периода');

        // Передвигаем свой указатель на строку ниже
        $this->currentRowNum = 3;
    }
}
