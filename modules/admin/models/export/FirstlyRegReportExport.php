<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 22.07.19
 * Time: 13:09
 */

namespace app\modules\admin\models\export;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class FirstlyRegReportExport
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

    const STYLE_SPECIESGREY = [ //светло-серая заливка (далее, каждый стиль с более темным оттенком серого)
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

    const STYLE_INFOROW = [
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_RIGHT,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
    ];

    const NO_AREA = 'Округ не найден';
    const NO_DISTRICT = 'Район не найден';
    const COL_COUNT = 6;

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
        $currentSpeciesId = false;
        $totals = [
            'totalPerSpec' => 0,
            'totalPerOrg' => 0,
            'totalPerDist' => 0,
            'totalPerArea' => 0,
            'total' => 0,
        ];

        foreach ($this->report as $report_row) { // идем поэлементно по массиву с результатами запроса
            // если данные по текущему виду животных кончились, выводим итог
            if($currentSpeciesId !== $report_row['idSpec']
                && $currentSpeciesId !== false
                || ($currentOrgId !== $report_row['id_reg_organization']
                    || $currentDistId !== $report_row['id_district']
                    ||  $currentAreaId !== $report_row['id_area'])
                && $currentSpeciesId !== false) {
                $this->_renderTotalRow(
                    'Итого по виду ' . $totals['totalPerSpec'],
                    self::STYLE_SPECIESGREY
                );
                $currentSpeciesId = false;
                $totals['totalPerSpec'] = 0;
            }

            // если данные в текущей организации кончились, выводим итог
            if($currentOrgId !== $report_row['id_reg_organization']
                && $currentOrgId !== false
                || (($currentDistId !== $report_row['id_district']
                    ||  $currentAreaId !== $report_row['id_area']))
                && $currentOrgId !== false) {
                $this->_renderTotalRow(
                    'Итого по организации ' . $totals['totalPerOrg'],
                    self::STYLE_ORGGREY
                );
                $currentOrgId = false;
                $currentSpeciesId = false;
                $totals['totalPerOrg'] = 0;
            }

            // если данные в текущем районе кончились, выводим итог
            if($currentDistId !== $report_row['id_district'] && $currentDistId !== false) {
                $this->_renderTotalRow(
                    'Итого по району ' . $totals['totalPerDist'],
                    self::STYLE_DISTGREY
                );
                $currentSpeciesId = false;
                $currentOrgId = false;
                $totals['totalPerDist'] = 0;
            }

            // если данные в текущем округе кончились, выводим итог
            if($currentAreaId !== $report_row['id_area'] && $currentAreaId !== false) {
                $this->_renderTotalRow(
                    'Итого по округу ' . $totals['totalPerArea'],
                    self::STYLE_AREAGREY
                );
                $currentSpeciesId = false;
                $currentOrgId = false;
                $totals['totalPerArea'] = 0;
            }

            // если начался новый округ, выводим его название
            if($currentAreaId !== $report_row['id_area']) {
                $currentAreaId = $report_row['id_area'];
                $this->_renderHeaderRow(
                    $report_row['areaName'] ?? self::NO_AREA,
                    self::STYLE_AREAGREY
                );
            }

            // если начался новый район, выводим его название
            if($currentDistId !== $report_row['id_district']) {
                $currentDistId = $report_row['id_district'];
                $this->_renderHeaderRow(
                    $report_row['distName'] ?? self::NO_DISTRICT,
                    self::STYLE_DISTGREY
                );
            }

            // если началась новая орагнизация, выводим ее название
            if($currentOrgId !== $report_row['id_reg_organization']) {
                $currentOrgId = $report_row['id_reg_organization'];
                $this->_renderHeaderRow(
                    $report_row['short_name'],
                    self::STYLE_ORGGREY
                );
            }

            // если начался новый вид животных, выводим его название
            if($currentSpeciesId !== $report_row['idSpec']) {
                $currentSpeciesId = $report_row['idSpec'];
                $this->_renderHeaderRow(
                    $report_row['specName'],
                    self::STYLE_SPECIESGREY
                );
            }

            $this->_renderRow($report_row['ownName'], $report_row);

            // ведем подсчет
            foreach ($totals as &$total) {
                $total++;
            }
        }

        /*
         * Итог по последнему виду животных
         */
        $this->_renderTotalRow(
            'Итого по виду ' . $totals['totalPerSpec'],
            self::STYLE_SPECIESGREY
        );

        /*
         * Итог по последней организации
         */
        $this->_renderTotalRow(
            'Итого по организации ' . $totals['totalPerOrg'],
            self::STYLE_ORGGREY
        );

        /*
         * Итог по последнему району
         */
        $this->_renderTotalRow(
            'Итого по району' . $totals['totalPerDist'],
            self::STYLE_DISTGREY
        );

        /*
         * Итог по последнему округу
         */
        $this->_renderTotalRow(
            'Итого по округу' . $totals['totalPerArea'],
            self::STYLE_AREAGREY
        );

        /*
         * ВСЕГО
         */
        $this->_renderTotalRow(
            'ВСЕГО ' . $totals['total'],
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

        // коэффициент для автовысоты ячейки, в которой выводится адрес владельца.
        // Предположим, что стандартной высоты хватит на 30 символов, иначе увеличиваем
        $coef = intdiv(mb_strlen($report_row['ownAddress']), 30) + 1;

        // проверка длины адреса. если длина менее 30 символов - оставляем стандартную высоту; если более 60 - увеличиваем вдвое; 90 - втрое и т.д.
        if ($coef >= 1) {
            $sheet
                ->getRowDimension($this->currentRowNum)
                ->setRowHeight(15 * $coef);
        }

        $sheet->fromArray(
            [
                $title,
                $report_row['ownAddress'],
                $report_row['ownPhone'],
                $report_row['petName'],
                $report_row['identType'],
                $report_row['identification_code'],
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
    protected function _renderTotalRow($title, $style = FALSE)
    {
        $sheet = $this->spreadsheet->getActiveSheet();

        $sheet->fromArray(
            [
                $title,
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

        $sheet->mergeCells('A' . $this->currentRowNum . ':F' . $this->currentRowNum);

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
            ->mergeCells('A1:F1')
            ->getColumnDimension('A');

        $sheet
            ->getStyle('B:B')
            ->getAlignment()->setWrapText(true); // Автоперенос

        $sheet
            ->getRowDimension('1')
            ->setRowHeight(30);

        $message = 'Отчет по первично зарегистрированным владельцам/животным с ' . $from . ' по ' . $to;
        $sheet->setCellValueByColumnAndRow(
            '1',
            $this->currentRowNum,
            $message
        );

        $sheet
            ->getColumnDimension('A')
            ->setWidth(50);

        $sheet
            ->getColumnDimension('B')
            ->setWidth(40);

        $sheet
            ->getColumnDimension('C')
            ->setWidth(20);

        $sheet
            ->getColumnDimension('D')
            ->setWidth(20);

        $sheet
            ->getColumnDimension('E')
            ->setWidth(23);

        $sheet
            ->getColumnDimension('F')
            ->setWidth(23);

        $sheet
            ->getRowDimension('2')
            ->setRowHeight(50); // Высота

        $sheet
            ->getStyle('A2:F2')
            ->getAlignment()->setWrapText(true); // Автоперенос

        $sheet
            ->getStyle('A1:F2')
            ->applyFromArray(self::STYLE_HEADER);

        $sheet
            ->getStyle('C:F')
            ->applyFromArray(self::STYLE_INFOROW);

        $sheet
            ->getStyle('F:F')
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_TEXT);

        $sheet
            ->setCellValue('A2', 'Владелец (ФЛ, ЮЛ)');

        $sheet
            ->setCellValue('B2', 'Адрес владельца');

        $sheet
            ->setCellValue('C2', 'Телефон владельца');

        $sheet
            ->setCellValue('D2', 'Кличка животного');

        $sheet
            ->setCellValue('E2', 'Тип метки (основной идентификатор животного)');

        $sheet
            ->setCellValue('F2', 'Значение метки');

        // Передвигаем свой указатель на строку ниже
        $this->currentRowNum = 3;
    }
}