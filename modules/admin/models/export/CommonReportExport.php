<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 15.07.19
 * Time: 15:45
 */

namespace app\modules\admin\models\export;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class CommonReportExport
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

    const COL_COUNT = 5;

    /**
     * @param $report
     * @param null $filename
     * @param $from
     * @param $to
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
        $totalPetsPerArea = 0;
        $totalPeriodPetsPerArea = 0;
        $totalVisitsPerArea = 0;
        $totalPeriodVisitsPerArea = 0;
        $totalPets = 0;
        $totalPeriodPets = 0;
        $totalVisits = 0;
        $totalPeriodVisits = 0;


        foreach ($this->report as $report_row) { // идем поэлементно по массиву с результатами запроса
//            если данные в текущем округе кончились, выводим итог
            if($currentAreaId !== $report_row['id_area'] && $currentAreaId !== false) {
                $this->_renderTotalRow(
                    'Итого по округу',
                    $totalPetsPerArea,
                    $totalPeriodPetsPerArea,
                    $totalVisitsPerArea,
                    $totalPeriodVisitsPerArea,
                    self::STYLE_LIGHTGREY
                );
                $currentAreaId = false;
                $totalPetsPerArea = 0;
                $totalPeriodPetsPerArea = 0;
                $totalVisitsPerArea = 0;
                $totalPeriodVisitsPerArea = 0;
            }

//            если начался новый округ, выводим его название
            if($currentAreaId !== $report_row['id_area']) {
                $currentAreaId = $report_row['id_area'];
                $this->_renderHeaderRow(
                    $report_row['name'] ?? self::NO_AREA,
                    self::STYLE_LIGHTGREY
                );
            }

            $this->_renderRow($report_row['short_name'], $report_row);

            // ведем подсчет по округу
            $totalPetsPerArea += $report_row['totalPets'];
            $totalPeriodPetsPerArea += $report_row['periodPets'];
            $totalVisitsPerArea += $report_row['totalVisits'];
            $totalPeriodVisitsPerArea += $report_row['periodVisits'];

            // ведем общий подсчет
            $totalPets += $report_row['totalPets'];
            $totalPeriodPets += $report_row['periodPets'];
            $totalVisits += $report_row['totalVisits'];
            $totalPeriodVisits += $report_row['periodVisits'];
        }

        /*
         * Итог по последнему округу
         */
        $this->_renderTotalRow(
            'Итого по округу',
            $totalPetsPerArea,
            $totalPeriodPetsPerArea,
            $totalVisitsPerArea,
            $totalPeriodVisitsPerArea,
            self::STYLE_LIGHTGREY
        );

        /*
         * ВСЕГО
         */
        $this->_renderTotalRow(
            'ВСЕГО',
            $totalPets,
            $totalPeriodPets,
            $totalVisits,
            $totalPeriodVisits,
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

        $sheet->fromArray(
            [
                $title,
                $report_row['totalPets'],
                $report_row['periodPets'],
                $report_row['totalVisits'],
                $report_row['periodVisits']
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
    protected function _renderTotalRow($title, $totalVar1, $totalVar2, $totalVar3, $totalVar4, $style = FALSE)
    {
        $sheet = $this->spreadsheet->getActiveSheet();

        $sheet->fromArray(
            [
                $title,
                $totalVar1,
                $totalVar2,
                $totalVar3,
                $totalVar4,
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
            ->mergeCells('A1:E1')
            ->getColumnDimension('A')
            ->setAutoSize(true); // Авто-ширина

        $sheet
            ->getRowDimension('1')
            ->setRowHeight(30);

        $message = 'Краткая статистика организаций по регистрации и приемам с ' . $from . ' по ' . $to;
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
            ->setWidth(20);
        $sheet
            ->getColumnDimension('C')
            ->setWidth(25);
        $sheet
            ->getColumnDimension('D')
            ->setWidth(16);
        $sheet
            ->getColumnDimension('E')
            ->setWidth(21);

        $sheet
            ->getRowDimension('2')
            ->setRowHeight(30); // Высота

        $sheet
            ->getStyle('A2:E2')
            ->getAlignment()->setWrapText(true); // Автоперенос

        $sheet
            ->getStyle('A1:E2')
            ->applyFromArray(self::STYLE_HEADER);

        $sheet
            ->getStyle('A:A')
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_TEXT)
        ;

        $sheet
            ->setCellValue('A2', 'Организация');

        $sheet
            ->setCellValue('B2', 'Зарегистрировано животных всего');

        $sheet
            ->setCellValue('C2', 'Зарегистрировано животных за период');

        $sheet
            ->setCellValue('D2', 'Приемов всего');

        $sheet
            ->setCellValue('E2', 'Приемов за период');

        // Передвигаем свой указатель на строку ниже
        $this->currentRowNum = 3;
    }

}
