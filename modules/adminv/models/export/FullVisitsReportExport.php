<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 19.07.19
 * Time: 14:50
 */

namespace app\modules\adminv\models\export;


use app\models\db\ShiftType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class FullVisitsReportExport
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

    protected $shift_types = [];

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

    const STYLE_MIDGREY = [
        'font' => [
            'bold' => true,
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => [
                'argb' => 'BEBEBE',
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

    const NO_AREA = 'Округ не найден';
    const NO_DISTRICT = 'Район не найден';

    const COL_COUNT = 41;

    public function __construct(){
        $this->shift_types = [
            strtolower(ShiftType::ASSIGN_SHIFT_TYPE_FOR_MOSRU_APPOINTMENT) => 'mos.ru',
            strtolower(ShiftType::ASSIGN_SHIFT_TYPE_FOR_PHONE_APPOINTMENT) => 'Телефон',
            strtolower(ShiftType::ASSIGN_SHIFT_TYPE_FOR_LIVE_QUEUE) => 'Живая очередь',
            strtolower(ShiftType::ASSIGN_SHIFT_TYPE_FOR_WORKDAY) => 'Направление',
            strtolower(ShiftType::ASSIGN_SHIFT_TYPE_FOR_CALL_TO_HOME) => 'Выезд на дом',
            strtolower(ShiftType::ASSIGN_SHIFT_TYPE_FOR_MOSRU_CALL_TO_HOME) => 'Выезд на дом (mos.ru)',
            strtolower(ShiftType::ASSIGN_SHIFT_TYPE_FOR_AMBULANCE) => 'Выезд на дом (НВП)',
            strtolower(ShiftType::ASSIGN_SHIFT_TYPE_FOR_VACCINATION_STATION) => 'Прививочный пункт',
            strtolower(ShiftType::ASSIGN_SHIFT_TYPE_FOR_SHELTER) => 'Выезд в приют',
            strtolower(ShiftType::ASSIGN_SHIFT_TYPE_FOR_DETOUR) => 'Обход',
        ];
    }
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

        $currentAreaId = false;
        $currentDistId = false;
        $total = [];
        $totalsPerDist = [];
        $totalsPerArea = [];

        foreach ($this->shift_types as $type => $name) {
            $total["{$type}_created"] = 0;
            $total["{$type}_cancelled"] = 0;
            $total["{$type}_transfered"] = 0;
            $total["{$type}_finished"] = 0;

            $totalsPerDist["{$type}_created"] = 0;
            $totalsPerDist["{$type}_cancelled"] = 0;
            $totalsPerDist["{$type}_transfered"] = 0;
            $totalsPerDist["{$type}_finished"] = 0;

            $totalsPerArea["{$type}_created"] = 0;
            $totalsPerArea["{$type}_cancelled"] = 0;
            $totalsPerArea["{$type}_transfered"] = 0;
            $totalsPerArea["{$type}_finished"] = 0;
        }


        foreach ($this->report as $report_row) { // идем поэлементно по массиву с результатами запроса
            // если данные в текущем районе кончились, выводим итог
            if($currentDistId !== $report_row['id_district'] && $currentDistId !== false) {
                $this->_renderTotalRow(
                    'Итого по району',
                    $totalsPerDist,
                    self::STYLE_LIGHTGREY
                );
                $currentDistId = false;
                foreach ($this->shift_types as $type => $name) {
                    $totalsPerDist["{$type}_created"] = 0;
                    $totalsPerDist["{$type}_cancelled"] = 0;
                    $totalsPerDist["{$type}_transfered"] = 0;
                    $totalsPerDist["{$type}_finished"] = 0;
                }
            }

            // если данные в текущем округе кончились, выводим итог
            if($currentAreaId !== $report_row['id_area'] && $currentAreaId !== false) {
                $this->_renderTotalRow(
                    'Итого по округу',
                    $totalsPerArea,
                    self::STYLE_LIGHTGREY
                );
                $currentDistId = false;
                foreach ($this->shift_types as $type => $name) {
                    $totalsPerArea["{$type}_created"] = 0;
                    $totalsPerArea["{$type}_cancelled"] = 0;
                    $totalsPerArea["{$type}_transfered"] = 0;
                    $totalsPerArea["{$type}_finished"] = 0;
                }
            }

            // если начался новый округ, выводим его название
            if($currentAreaId !== $report_row['id_area']) {
                $currentAreaId = $report_row['id_area'];
                $this->_renderHeaderRow(
                    $report_row['area_name'] ?? self::NO_AREA,
                    self::STYLE_MIDGREY
                );
            }

            // если начался новый район, выводим его название
            if($currentDistId !== $report_row['id_district']) {
                $currentDistId = $report_row['id_district'];
                $this->_renderHeaderRow(
                    $report_row['dist_name'] ?? self::NO_DISTRICT,
                    self::STYLE_LIGHTGREY
                );
            }

            $this->_renderRow($report_row['short_name'], $report_row);

            // ведем подсчет по району
            foreach ($this->shift_types as $type => $name) {
                $total["{$type}_created"] += $report_row["{$type}_created"];
                $total["{$type}_cancelled"] += $report_row["{$type}_cancelled"];
                $total["{$type}_transfered"] += $report_row["{$type}_transfered"];
                $total["{$type}_finished"] += $report_row["{$type}_finished"];

                $totalsPerDist["{$type}_created"] += $report_row["{$type}_created"];
                $totalsPerDist["{$type}_cancelled"] += $report_row["{$type}_cancelled"];
                $totalsPerDist["{$type}_transfered"] += $report_row["{$type}_transfered"];
                $totalsPerDist["{$type}_finished"] += $report_row["{$type}_finished"];

                $totalsPerArea["{$type}_created"] += $report_row["{$type}_created"];
                $totalsPerArea["{$type}_cancelled"] += $report_row["{$type}_cancelled"];
                $totalsPerArea["{$type}_transfered"] += $report_row["{$type}_transfered"];
                $totalsPerArea["{$type}_finished"] += $report_row["{$type}_finished"];
            }
        }

        /*
         * Итог по последнему району
         */
        $this->_renderTotalRow(
            'Итого по району',
            $totalsPerDist,
            self::STYLE_LIGHTGREY
        );

        /*
         * Итог по последнему округу
         */
        $this->_renderTotalRow(
            'Итого по округу',
            $totalsPerArea,
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

        // коэффициент для автовысоты ячейки, в которой выводится длинное название организации.
        // Предположим, что стандартной высоты хватит на 20 символов, иначе увеличиваем
        $coef = intdiv(mb_strlen($title), 20) + 1;

        // проверка длины названия организации. если длина менее 20 символов - оставляем стандартную высоту; если более 40 - увеличиваем вдвое; 60 - втрое и т.д.
        if ($coef >= 1) {
            $sheet
                ->getRowDimension($this->currentRowNum)
                ->setRowHeight(15 * $coef);
        }

        $render_row = [];
        foreach ($this->shift_types as $type => $name) {
            $render_row["{$type}_created"] = ltrim(trim($report_row["{$type}_created"]), '=-+^');
            $render_row["{$type}_cancelled"] = ltrim(trim($report_row["{$type}_cancelled"]), '=-+^');
            $render_row["{$type}_transfered"] = ltrim(trim($report_row["{$type}_transfered"]), '=-+^');
            $render_row["{$type}_finished"] = ltrim(trim($report_row["{$type}_finished"]), '=-+^');
        }

        $sheet->fromArray(
            array_merge([$title], $render_row),
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
     * @param array $row
     * @param bool $style
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function _renderTotalRow($title, array $row, $style = FALSE)
    {
        $sheet = $this->spreadsheet->getActiveSheet();

        $sheet->fromArray(
            array_merge([$title], $row),
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

        $sheet->mergeCells('A' . $this->currentRowNum . ':AO' . $this->currentRowNum);

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

        //первая строка
        $sheet
            ->mergeCells('A1:AO1')
            ->getColumnDimension('A');

        $sheet
            ->getRowDimension('1')
            ->setRowHeight(30);

        $message = 'Общий отчет по приемам за период с ' . $from . ' по ' . $to;
        $sheet->setCellValueByColumnAndRow(
            '1',
            $this->currentRowNum,
            $message
        );

        //вторая строка
        $sheet
            ->mergeCells('A2:AO2')
            ->getColumnDimension('A');

        $sheet
            ->setCellValue('A2', 'Обработано обращений (записей на прием)');

        //третья и четвертая строки
        $statuses = [
            'Всего',
            'Отменено',
            'Перенесено',
            'Завершено'
        ];
        $firts_col = 'B';
        $last_col = 'E';

        foreach ($this->shift_types as $type => $name){
            $sheet
                ->mergeCells("{$firts_col}3:{$last_col}3")
                ->setCellValue("{$firts_col}3", $name);

            for ($i = 0; $i < 4; $i++){
                $sheet
                    ->setCellValue("{$firts_col}4", $statuses[$i])
                    ->getColumnDimension($firts_col)
                    ->setAutoSize(true);
                $firts_col++;
                $last_col++;
            }
        }

        $sheet
            ->getStyle('A2:AO4')
            ->getAlignment()->setWrapText(true); // Автоперенос

        $sheet
            ->getStyle('A1:AO4')
            ->applyFromArray(self::STYLE_HEADER);

        $sheet
            ->getStyle('A:A')
            ->getAlignment()->setWrapText(true); // Автоперенос для длинных организаций

        $sheet
            ->getColumnDimension('A')
            ->setWidth(30);



        // Передвигаем свой указатель на строку ниже
        $this->currentRowNum = 5;
    }

}
