<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 16.07.19
 * Time: 14:20
 */

namespace app\modules\admin\models\export;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ServicesReportExport
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

    const STYLE_TYPEGREY = [ //светло-серая заливка (далее, каждый стиль с более темным оттенком серого)
        'font' => [
            'bold' => true,
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => [
                'argb' => 'D6D6D6',
            ],
        ]
    ];

    const STYLE_ORGGREY = [
        'font' => [
            'bold' => true,
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => [
                'argb' => 'ABABAB',
            ],
        ]
    ];

    const STYLE_DISTGREY = [
        'font' => [
            'bold' => true,
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => [
                'argb' => '808080',
            ],
        ]
    ];

    const STYLE_AREAGREY = [
        'font' => [
            'bold' => true,
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => [
                'argb' => '656565',
            ],
        ]
    ];

    const STYLE_TOTALGRAY = [
        'font' => [
            'bold' => true,
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => [
                'argb' => '5F5F5F',
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

    const COL_COUNT = 4;


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

        $currentAreaId = false;
        $currentDistId = false;
        $currentOrgId = false;
        $currentTypeId = false;
        $totals = [
            'totalPerType' => 0,
            'totalPerOrg' => 0,
            'totalCountServicesPerType' => 0,
            'totalCountServicesPerOrg' => 0,
            'totalCountServicesPerDist' => 0,
            'totalCountServicesPerArea' => 0,
            'totalSumAmountPerType' => 0,
            'totalSumAmountPerOrg' => 0,
            'totalSumAmountPerDist' => 0,
            'totalSumAmountPerArea' => 0,
            'totalCountServices' => 0,
            'totalSumAmount' => 0,
            'serviceNames' => [],
            'serviceNamesPerArea' => [],
            'serviceNamesPerDist' => []
        ];

        foreach ($this->report as $report_row) { // идем поэлементно по массиву с результатами запроса
            // если данные в текущем типе услуг кончились, выводим итог
            if($currentTypeId !== $report_row['type_id']
                && $currentTypeId !== false
                || ($currentOrgId !== $report_row['id_organization']
                    || $currentDistId !== $report_row['id_district']
                    ||  $currentAreaId !== $report_row['id_area'])
                    && $currentTypeId !== false
            ) {
                $this->_renderTotalRow(
                    'Итого по типу ' . $totals['totalPerType'],
                    $totals['totalCountServicesPerType'],
                    $totals['totalSumAmountPerType'],
                    self::STYLE_TYPEGREY
                );
                $currentTypeId = false;
                $totals['totalPerType'] = 0;
                $totals['totalCountServicesPerType'] = 0;
                $totals['totalSumAmountPerType'] = 0;
            }

            // если данные в текущей организации кончились, выводим итог
            if($currentOrgId !== $report_row['id_organization']
                && $currentOrgId !== false
                || (($currentDistId !== $report_row['id_district']
                    ||  $currentAreaId !== $report_row['id_area']))
                && $currentOrgId !== false) {
                $this->_renderTotalRow(
                    'Итого по организации ' . $totals['totalPerOrg'],
                    $totals['totalCountServicesPerOrg'],
                    $totals['totalSumAmountPerOrg'],
                    self::STYLE_ORGGREY
                );
                $currentOrgId = false;
                $currentTypeId = false;
                $totals['totalPerOrg'] = 0;
                $totals['totalCountServicesPerOrg'] = 0;
                $totals['totalSumAmountPerOrg'] = 0;
            }

            // если данные в текущем районе кончились, выводим итог
            if($currentDistId !== $report_row['id_district'] && $currentDistId !== false) {
                $this->_renderTotalRow(
                    'Итого по району ' . count(array_unique($totals['serviceNamesPerDist'])),
                    $totals['totalCountServicesPerDist'],
                    $totals['totalSumAmountPerDist'],
                    self::STYLE_DISTGREY
                );
                $currentTypeId = false;
                $currentOrgId = false;
                $totals['serviceNamesPerDist'] = [];
                $totals['totalCountServicesPerDist'] = 0;
                $totals['totalSumAmountPerDist'] = 0;
            }

            // если данные в текущем округе кончились, выводим итог
            if($currentAreaId !== $report_row['id_area'] && $currentAreaId !== false) {
                $this->_renderTotalRow(
                    'Итого по округу ' . count(array_unique($totals['serviceNamesPerArea'])),
                    $totals['totalCountServicesPerArea'],
                    $totals['totalSumAmountPerArea'],
                    self::STYLE_AREAGREY
                );
                $currentTypeId = false;
                $currentOrgId = false;
                $totals['serviceNamesPerArea'] = [];
                $totals['totalCountServicesPerArea'] = 0;
                $totals['totalSumAmountPerArea'] = 0;
            }

            // если начался новый округ, выводим его название
            if($currentAreaId !== $report_row['id_area']) {
                $currentAreaId = $report_row['id_area'];
                $this->_renderHeaderRow(
                    $report_row['area'] ?? self::NO_AREA,
                    self::STYLE_AREAGREY
                );
            }

            // если начался новый район, выводим его название
            if($currentDistId !== $report_row['id_district']) {
                $currentDistId = $report_row['id_district'];
                $this->_renderHeaderRow(
                    $report_row['dist'] ?? self::NO_DISTRICT,
                    self::STYLE_DISTGREY
                );
            }

            // если началась новая орагнизация, выводим ее название
            if($currentOrgId !== $report_row['id_organization']) {
                $currentOrgId = $report_row['id_organization'];
                $this->_renderHeaderRow(
                    $report_row['short_name'],
                    self::STYLE_ORGGREY
                );
            }

            // если начался новый тип услуг, выводим его название
            if($currentTypeId !== $report_row['type_id']) {
                $currentTypeId = $report_row['type_id'];
                $this->_renderHeaderRow(
                    $report_row['type_name'],
                    self::STYLE_TYPEGREY
                );
            }

            $this->_renderRow($report_row['name'], $report_row);

            // ведем подсчет по типу услуг
            $totals['totalPerType']++;
            $totals['totalCountServicesPerType'] += $report_row['sum'];
            $totals['totalSumAmountPerType'] += $report_row['total_amount'];

            // ведем подсчет по организации
            $totals['totalPerOrg']++;
            $totals['totalCountServicesPerOrg'] += $report_row['sum'];
            $totals['totalSumAmountPerOrg'] += $report_row['total_amount'];

            // ведем подсчет по району
            $totals['serviceNamesPerDist'][] = $report_row['name'] ;
            $totals['totalCountServicesPerDist'] += $report_row['sum'];
            $totals['totalSumAmountPerDist'] += $report_row['total_amount'];

            // ведем подсчет по округу
            $totals['serviceNamesPerArea'][] = $report_row['name'] ;
            $totals['totalCountServicesPerArea'] += $report_row['sum'];
            $totals['totalSumAmountPerArea'] += $report_row['total_amount'];

            // ведем общий подсчет
            $totals['serviceNames'][] = $report_row['name'] ;
            $totals['totalCountServices'] += $report_row['sum'];
            $totals['totalSumAmount'] += $report_row['total_amount'];
        }

        /*
         * Итог по последнему типу услуг
         */
        $this->_renderTotalRow(
            $report_row['type_name'] . ': итого ' . $totals['totalPerType'],
            $totals['totalCountServicesPerType'],
            $totals['totalSumAmountPerType'],
            self::STYLE_TYPEGREY
        );

        /*
         * Итог по последней организации
         */
        $this->_renderTotalRow(
            'Итого по организации ' . $totals['totalPerOrg'],
            $totals['totalCountServicesPerOrg'],
            $totals['totalSumAmountPerOrg'],
            self::STYLE_ORGGREY
        );

        /*
         * Итог по последнему району
         */
        $this->_renderTotalRow(
            'Итого по району' . count(array_unique($totals['serviceNamesPerDist'])),
            $totals['totalCountServicesPerDist'],
            $totals['totalSumAmountPerDist'],
            self::STYLE_DISTGREY
        );

        /*
         * Итог по последнему округу
         */
        $this->_renderTotalRow(
            'Итого по округу' . count(array_unique($totals['serviceNamesPerArea'])),
            $totals['totalCountServicesPerArea'],
            $totals['totalSumAmountPerArea'],
            self::STYLE_AREAGREY
        );

        /*
         * ВСЕГО
         */
        $this->_renderTotalRow(
            'ВСЕГО ' . count(array_unique($totals['serviceNames'])),
            $totals['totalCountServices'],
            $totals['totalSumAmount'],
            self::STYLE_TOTALGRAY
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
        // Предположим, что стандартной высоты хватит на 50 символов, иначе увеличиваем
        $coef = intdiv(mb_strlen($title), 50) + 1;

        // проверка длины названия услуги. если длина менее 50 символов - оставляем стандартную высоту; если более 100 - увеличиваем вдвое; 150 - втрое и т.д.
        if ($coef >= 1) {
            $sheet
                ->getRowDimension($this->currentRowNum)
                ->setRowHeight(15 * $coef);
        }

        $sheet->fromArray(
            [
                $title,
                $report_row['sum'],
                $report_row['price'],
                $report_row['total_amount'],

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
     * @param $totalVar1
     * @param $totalVar2
     * @param bool $style
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function _renderTotalRow($title, $totalVar1, $totalVar2, $style = FALSE)
    {
        $sheet = $this->spreadsheet->getActiveSheet();

        $sheet->fromArray(
            [
                $title,
                $totalVar1,
                '',
                $totalVar2
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
            ->mergeCells('A1:D1')
            ->getColumnDimension('A');

        $sheet
            ->getStyle('A:A')
            ->getAlignment()->setWrapText(true); // Автоперенос

        $sheet
            ->getRowDimension('1')
            ->setRowHeight(30);

        $message = 'Отчет по контролю спроса с ' . $from . ' по ' . $to;
        $sheet->setCellValueByColumnAndRow(
            '1',
            $this->currentRowNum,
            $message
        );

        $sheet
            ->getColumnDimension('A')
            ->setWidth(70);

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
            ->getRowDimension('2')
            ->setRowHeight(30); // Высота

        $sheet
            ->getStyle('A2:D2')
            ->getAlignment()->setWrapText(true); // Автоперенос

        $sheet
            ->getStyle('A1:D2')
            ->applyFromArray(self::STYLE_HEADER);

        $sheet
            ->getStyle('A:A')
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_TEXT)
        ;

        $sheet
            ->setCellValue('A2', 'Услуга');

        $sheet
            ->setCellValue('B2', 'Оказано количество');

        $sheet
            ->setCellValue('C2', 'Стоимость за единицу');

        $sheet
            ->setCellValue('D2', 'Стоимость услуг (общая)');

        // Передвигаем свой указатель на строку ниже
        $this->currentRowNum = 3;
    }
}