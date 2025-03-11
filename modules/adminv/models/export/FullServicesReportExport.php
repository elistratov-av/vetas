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

class FullServicesReportExport
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


    const COL_COUNT = 13;


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
            'totalLQ' => 0,
            'totalPhone' => 0,
            'totalWorkday' => 0,
            'totalMosRu' => 0,
            'totalMPGU' => 0,
            'totalAmb' => 0,
            'totalHomeMos' => 0,
            'totalVaccStation' => 0,
            'totalDetour' => 0,
            'totalShelter' => 0,
        ];


        foreach ($this->report as $report_row) { // идем поэлементно по массиву с результатами запроса

            $this->_renderRow($report_row['service_name'], $report_row);

            $totals['totalLQ'] += $report_row['total_lq'];
            $totals['totalPhone'] += $report_row['total_phone'];
            $totals['totalWorkday'] += $report_row['total_workday'];
            $totals['totalMosRu'] += $report_row['total_mosru'];
            $totals['totalMPGU'] += $report_row['total_mpgu'];
            $totals['totalAmb'] += $report_row['total_ambulance'];
            $totals['totalHomeMos'] += $report_row['total_home_mosru'];
            $totals['totalVaccStation'] += $report_row['total_vacc_station'];
            $totals['totalDetour'] += $report_row['total_detour'];
            $totals['totalShelter'] += $report_row['total_shelter'];
        }

        /*
         * ВСЕГО
         */
        $this->_renderTotalRow(
            'ВСЕГО',
            $totals['totalLQ'],
            $totals['totalPhone'],
            $totals['totalWorkday'],
            $totals['totalMosRu'],
            $totals['totalMPGU'],
            $totals['totalAmb'],
            $totals['totalHomeMos'],
            $totals['totalVaccStation'],
            $totals['totalDetour'],
            $totals['totalShelter'],
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
                $report_row['service_name'],
                $report_row['total_lq'],
                $report_row['total_phone'],
                $report_row['total_workday'],
                $report_row['total_mosru'],
                $report_row['total_mpgu'],
                $report_row['total_ambulance'],
                $report_row['total_home_mosru'],
                $report_row['total_vacc_station'],
                $report_row['total_detour'],
                $report_row['total_shelter'],
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
     * @param $totalVar1-10
     * @param bool $style
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function _renderTotalRow($title, $totalVar1, $totalVar2, $totalVar3, $totalVar4, $totalVar5, $totalVar6, $totalVar7, $totalVar8, $totalVar9, $totalVar10, $style = FALSE)
    {
        $sheet = $this->spreadsheet->getActiveSheet();

        $sheet->fromArray(
            [
                $title,
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
            ->mergeCells('A1:M1')
            ->getColumnDimension('A');

        $sheet
            ->getRowDimension('1')
            ->setRowHeight(30);

        $message = 'Общий отчет по услугам за период с ' . $from . ' по ' . $to;
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
            ->setWidth(30);

        $sheet
            ->setCellValue('C5', 'Название услуги');

        $sheet
            ->mergeCells('D5:M5')
            ->getColumnDimension('D');

        $sheet
            ->setCellValue('D5', 'Количество записей');


        //шестая строка

        $sheet
            ->getRowDimension(6)
            ->setRowHeight(33);

        $sheet
            ->getColumnDimension('D')
            ->setWidth(14);

        $sheet
            ->setCellValue('D6', 'Запись по ЖО');

        $sheet
            ->getColumnDimension('E')
            ->setWidth(14);

        $sheet
            ->setCellValue('E6', 'Запись по телефону');

        $sheet
            ->getColumnDimension('F')
            ->setWidth(14);

        $sheet
            ->setCellValue('F6', 'Запись по направлению');

        $sheet
            ->getColumnDimension('G')
            ->setWidth(14);

        $sheet
            ->setCellValue('G6', 'Запись на портале mos.ru');

        $sheet
            ->getColumnDimension('H')
            ->setWidth(19);

        $sheet
            ->setCellValue('H6', 'Запись в мобильном приложении');

        $sheet
            ->getColumnDimension('I')
            ->setWidth(19);

        $sheet
            ->setCellValue('I6', 'Ветеринарная помощь на дому');

        $sheet
            ->getColumnDimension('J')
            ->setWidth(19);

        $sheet
            ->setCellValue('J6', 'Вызов на дом (mos.ru)');

        $sheet
            ->getColumnDimension('K')
            ->setWidth(19);

        $sheet
            ->setCellValue('K6', 'Прививочные пункты');

        $sheet
            ->getColumnDimension('L')
            ->setWidth(19);

        $sheet
            ->setCellValue('L6', 'Обходы');

        $sheet
            ->getColumnDimension('M')
            ->setWidth(19);

        $sheet
            ->setCellValue('M6', 'Приюты');

        $sheet
            ->getStyle('A5:M6')
            ->getAlignment()->setWrapText(true); // Автоперенос

        $sheet
            ->getStyle('A1:M6')
            ->applyFromArray(self::STYLE_HEADER);

        $sheet
            ->getStyle('C:C')
            ->getAlignment()->setWrapText(true); // Автоперенос для длинных организаций

        // Передвигаем свой указатель на строку ниже
        $this->currentRowNum = 7;
    }

}
