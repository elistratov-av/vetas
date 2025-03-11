<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 19.07.19
 * Time: 14:50
 */

namespace app\modules\adminv\models\export;


use app\models\db\Visits;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class FullServicesVol2ReportExport
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

    protected $countVisits;
    protected $countNew;
    protected $countWork;

    /**
     * @var int Номер текущей строки
     */
    protected $currentRowNum = 1;

    const STYLE_TOTAL_BLUE = [
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


    const COL_COUNT = 14;


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

        $totals = [
            'total' => 0,
            'totalF' => 0,
            'totalA' => 0,
            'totalN' => 0,
            'totalW' => 0,
            'totalT' => 0,
            'totalC' => 0,
            'totalD' => 0,
        ];


        foreach ($this->report as $report_row) { // идем поэлементно по массиву с результатами запроса

            $this->_renderRow($report_row['service_name'], $report_row);

            $totals['total'] += $report_row['total_services'];
            $totals['totalF'] += $report_row['total_f'];
            $totals['totalA'] += $report_row['total_a'];
            $totals['totalN'] += $report_row['total_n'];
            $totals['totalW'] += $report_row['total_w'];
            $totals['totalT'] += $report_row['total_t'];
            $totals['totalC'] += $report_row['total_c'];
            $totals['totalD'] += $report_row['total_d'];
        }

        /*
         * ВСЕГО
         */
        $this->_renderTotalRow(
            'ВСЕГО',
            $totals['total'],
            $totals['totalF'],
            $totals['totalA'],
            $totals['totalN'],
            $totals['totalW'],
            $totals['totalT'],
            $totals['totalC'],
            $totals['totalD'],
            self::STYLE_TOTAL_BLUE
        );
    }

    /**
     * @param $title
     * @param $report_row
     * @param bool $style
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function _renderRow($service_name, $report_row, $style = FALSE)
    {
        $sheet = $this->spreadsheet->getActiveSheet();

        $months = [
            '01' => 'Январь',
            '02' => 'Февраль',
            '03' => 'Март',
            '04' => 'Апрель',
            '05' => 'Май',
            '06' => 'Июнь',
            '07' => 'Июль',
            '08' => 'Август',
            '09' => 'Сентябрь',
            '10' => 'Октябрь',
            '11' => 'Ноябрь',
            '12' => 'Декабрь',
        ];

        // коэффициент для автовысоты ячейки, в которой выводится длинное название организации.
        // Предположим, что стандартной высоты хватит на 25 символов, иначе увеличиваем
        $coef = intdiv(mb_strlen($service_name), 25) + 1;

        // проверка длины названия услуги. если длина менее 25 символов - оставляем стандартную высоту;
        // если более 50 - увеличиваем вдвое; 75 - втрое и т.д.
        if ($coef >= 1) {
            $sheet
                ->getRowDimension($this->currentRowNum)
                ->setRowHeight(15 * $coef);
        }

        $year = substr($report_row['date'], 0, 4);
        $monthNum = substr($report_row['date'], -2);
        $month = $months[$monthNum];

        foreach ($report_row as $key => $value) {
            $value = trim($value);
            $value = ltrim($value, '=-+^');
            $report_row[$key] = $value;
        }

        $sheet->fromArray(
            [
                $year,
                $month,
                $report_row['area_name'],
                $report_row['short_name'],
                $report_row['service_name'],
                $report_row['channel'],
                $report_row['total_services'],
                $report_row['total_f'],
                $report_row['total_a'],
                $report_row['total_n'],
                $report_row['total_w'],
                $report_row['total_t'],
                $report_row['total_c'],
                $report_row['total_d'],
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
                                       $totalVar6,
                                       $totalVar7,
                                       $totalVar8,
                                       $style = FALSE)
    {
        $sheet = $this->spreadsheet->getActiveSheet();

        $sheet->fromArray(
            [
                $title,
                '',
                '',
                '',
                '',
                '',
                $totalVar1,
                $totalVar2,
                $totalVar3,
                $totalVar4,
                $totalVar5,
                $totalVar6,
                $totalVar7,
                $totalVar8,
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
        $this->countVisits = Visits::find()
            ->count();
        $this->countNew = Visits::find()
            ->andWhere(['status' => 'N'])
            ->count();
        $this->countWork = Visits::find()
            ->andWhere(['status' => 'W'])
            ->count();

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

        $message = 'Детальный отчет по услугам за период с ' . $from . ' по ' . $to;
        $sheet->setCellValueByColumnAndRow(
            '1',
            $this->currentRowNum,
            $message
        );

        //третья строка
        $sheet
            ->mergeCells('A3:B3')
            ->getColumnDimension('A');

        $sheet
            ->setCellValue('A3', 'Всего заявок:' . $this->countVisits);

        $sheet
            ->mergeCells('C3:D3')
            ->getColumnDimension('C');

        $sheet
            ->setCellValue('C3', 'Новых заявок:' . $this->countNew);

        $sheet
            ->mergeCells('E3:F3')
            ->getColumnDimension('E');

        $sheet
            ->setCellValue('E3', 'Заявок в работе:' . $this->countWork);

        //пятая строка
        $sheet
            ->mergeCells('A5:A6')
            ->getColumnDimension('A');

        $sheet
            ->getColumnDimension('A')
            ->setWidth(15);

        $sheet
            ->setCellValue('A5', 'Год');

        $sheet
            ->mergeCells('B5:B6')
            ->getColumnDimension('B');

        $sheet
            ->getColumnDimension('B')
            ->setWidth(15);

        $sheet
            ->setCellValue('B5', 'Месяц');

        $sheet
            ->mergeCells('C5:C6')
            ->getColumnDimension('C');

        $sheet
            ->getColumnDimension('C')
            ->setWidth(22);

        $sheet
            ->setCellValue('C5', 'Административный округ');

        $sheet
            ->mergeCells('D5:D6')
            ->getColumnDimension('D');

        $sheet
            ->getColumnDimension('D')
            ->setWidth(20);

        $sheet
            ->setCellValue('D5', 'Организация');

        $sheet
            ->mergeCells('E5:E6')
            ->getColumnDimension('E');

        $sheet
            ->getColumnDimension('E')
            ->setWidth(30);

        $sheet
            ->setCellValue('E5', 'Название услуги');

        $sheet
            ->mergeCells('F5:F6')
            ->getColumnDimension('F');

        $sheet
            ->getColumnDimension('F')
            ->setWidth(15);

        $sheet
            ->setCellValue('F5', 'Канал записи');

        $sheet
            ->mergeCells('G5:N5')
            ->getColumnDimension('G');

        $sheet
            ->setCellValue('G5', 'Статус заявки');

        //шестая строка

        $sheet
            ->getRowDimension(6)
            ->setRowHeight(33);

        $sheet
            ->getColumnDimension('G')
            ->setWidth(10);

        $sheet
            ->setCellValue('G6', 'Всего');

        $sheet
            ->getColumnDimension('H')
            ->setWidth(11);

        $sheet
            ->setCellValue('H6', 'Завершено');

        $sheet
            ->getColumnDimension('I')
            ->setWidth(10);

        $sheet
            ->setCellValue('I6', 'Отменено');

        $sheet
            ->getColumnDimension('J')
            ->setWidth(10);

        $sheet
            ->setCellValue('J6', 'Новый');

        $sheet
            ->getColumnDimension('K')
            ->setWidth(10);

        $sheet
            ->setCellValue('K6', 'В работе');

        $sheet
            ->getColumnDimension('L')
            ->setWidth(10);

        $sheet
            ->setCellValue('L6', 'К переносу');

        $sheet
            ->getColumnDimension('M')
            ->setWidth(10);

        $sheet
            ->setCellValue('M6', 'Изменено');

        $sheet
            ->getColumnDimension('N')
            ->setWidth(10);

        $sheet
            ->setCellValue('N6', 'Закрыто по тайм-ауту');

        $sheet
            ->getStyle('A5:N6')
            ->getAlignment()->setWrapText(true); // Автоперенос

        $sheet
            ->getStyle('A1:N6')
            ->applyFromArray(self::STYLE_HEADER);

        $sheet
            ->getStyle('C:C')
            ->getAlignment()->setWrapText(true); // Автоперенос для длинных AO

        $sheet
            ->getStyle('E:E')
            ->getAlignment()->setWrapText(true); // Автоперенос для длинных услуг

        // Передвигаем свой указатель на строку ниже
        $this->currentRowNum = 7;
    }

}
