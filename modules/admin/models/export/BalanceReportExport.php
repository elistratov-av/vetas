<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 19.07.19
 * Time: 12:31
 */

namespace app\modules\admin\models\export;


use app\modules\admin\models\Organization;
use app\modules\admin\models\OrganizationsTree;
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

    const COL_COUNT = 7;


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
        $pathOrgs = $this->report[1];
        $orderedRows = $this->report[2];
        $orderedData = $this->report[3];
        $tree = $this->report[4];
        $organizations = Organization::find()
            ->select('short_name')
            ->orderBy(['name' => SORT_ASC])
            ->indexBy('id')
            ->asArray()
            ->column();

        foreach ($tree as $leaf) {
            if (in_array($leaf, $pathOrgs)) { //выводим полный иерархический путь до организации с ее итоговыми цифрами
                $level = OrganizationsTree::find()->select('level')->where(['id' => $leaf])->asArray()->indexBy('id')->column();
                $orgLevel = $level[$leaf];
                $result = '';
                for($i = 1; $i <= $orgLevel; $i++) { //добавляем отступ (2 пробела) перед названием организации согласно иерархии
                    $result = $result . "  ";
                }
                $this->_renderHeaderRow(
                    $result . $organizations[$leaf] ?? "Название не найдено",
                    '',
                    '',
                    $orderedRows[$leaf][0]['totalstart'] ?? 0,
                    $orderedRows[$leaf][0]['totalgot'] ?? 0,
                    $orderedRows[$leaf][0]['totalspent'] ?? 0,
                    $orderedRows[$leaf][0]['totalend'] ?? 0,
                    self::STYLE_LIGHTGREY
                );
            }
            if (array_key_exists($leaf, $orderedData)) { //если у организации есть препараты на балансе, выводим их в цикле
                foreach ($orderedData[$leaf] as $orderedDatum) {
                    $this->_renderRow($orderedDatum);
                }
            }
        }
    }

    /**
     * @param $orderedDatum
     * @param bool $style
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function _renderRow($orderedDatum, $style = FALSE)
    {
        $sheet = $this->spreadsheet->getActiveSheet();

        // коэффициент для автовысоты ячейки, в которой выводится длинное название ячейки.
        // Предположим, что стандартной высоты хватит на 45 символов, иначе увеличиваем
        $coef = intdiv(mb_strlen($orderedDatum['drug_name']), 45) + 1;

        // проверка длины названия препарата. если длина менее 45 символов - оставляем стандартную высоту; если более 90 - увеличиваем вдвое; 135 - втрое и т.д.
        if ($coef >= 1) {
            $sheet
                ->getRowDimension($this->currentRowNum)
                ->setRowHeight(15 * $coef);
        }

        $sheet->fromArray(
            [
                $orderedDatum['drug_name'],
                $orderedDatum['name'],
                $orderedDatum['balanceperiodstart'] ?? 0,
                $orderedDatum['got'] ?? 0,
                $orderedDatum['spent'] ?? 0,
                $orderedDatum['balanceperiodend'] ?? 0,
            ],
            NULL,
            'B' . $this->currentRowNum,
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
     * @param $totalVar5
     * @param $totalVar6
     * @param bool $style
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function _renderHeaderRow($title, $totalVar1, $totalVar2, $totalVar3, $totalVar4, $totalVar5, $totalVar6, $style = FALSE)
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
            ->mergeCells('A1:G1')
            ->getColumnDimension('A')
            ->setAutoSize(true); // Авто-ширина

        $sheet
            ->getRowDimension('1')
            ->setRowHeight(30);

        $sheet
            ->getStyle('B:B')
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
            ->setWidth(50);

        $sheet
            ->getColumnDimension('C')
            ->setWidth(7);

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
            ->getRowDimension('2')
            ->setRowHeight(30); // Высота

        $sheet
            ->getStyle('A2:G2')
            ->getAlignment()->setWrapText(true); // Автоперенос

        $sheet
            ->getStyle('A1:G2')
            ->applyFromArray(self::STYLE_HEADER);

        $sheet
            ->getStyle('A:A')
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_TEXT);

        $sheet
            ->setCellValue('B2', 'Препарат (ТМЦ)');

        $sheet
            ->setCellValue('C2', 'Ед. изм.');

        $sheet
            ->setCellValue('D2', 'Остаток на начало периода');

        $sheet
            ->setCellValue('E2', 'Поступило');

        $sheet
            ->setCellValue('F2', 'Расход');

        $sheet
            ->setCellValue('G2', 'Остаток на конец периода');

        // Передвигаем свой указатель на строку ниже
        $this->currentRowNum = 3;
    }
}