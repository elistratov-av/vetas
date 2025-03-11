<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 22.07.19
 * Time: 14:09
 */

namespace app\modules\adminv\models\export;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use yii\db\Expression;

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
    protected $rows = [];
    /**
     * @var \yii\db\ActiveQuery
     */
    protected $query;
    /**
     * @var int Номер текущей строки
     */
    protected $currentRowNum = 1;

    /**
     * @var int
     */
    private $currentAreaId = false;
    /**
     * @var int
     */
    private $currentDistId = false;
    /**
     * @var int
     */
    private $currentOrgId = false;
    /**
     * @var int
     */
    private $currentSpeciesId = false;
    /**
     * @var array
     */
    private $totals = [
        'totalPerSpec' => 0,
        'totalPerOrg' => 0,
        'totalPerDist' => 0,
        'totalPerArea' => 0,
        'total' => 0,
    ];

    const STYLE_SPECIESGREY = [ //светло-серая заливка (далее, каждый стиль с более темным оттенком серого)
        'font' => [
            'bold' => true,
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => [
                'argb' => 'D6D6D6',
            ],
        ],
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
        ],
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
        ],
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
        ],
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
        ],
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
     * @param \yii\db\ActiveQuery $query
     * @param string              $filename
     * @param string              $from
     * @param string              $to
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\web\RangeNotSatisfiableHttpException
     */
    public function export($query, $filename, $from, $to)
    {
        @ini_set('memory_limit', '512M');

        $this->spreadsheet = new Spreadsheet();
        $this->query = $query;

        $this->renderReport($from, $to);

        $this->sendXlsx($this->spreadsheet, $filename);
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

        $query = clone $this->query;
        $query->select(new Expression('COUNT(*)'));
        $query->orderBy([]);
        $total = $query->scalar();

        if ($total == 0) {
            $this->renderBody(true);
        } else {
            $batchSize = 10;
            $batches = (int)ceil($total / $batchSize);
            $i = 0;
            foreach ($this->query->batch($batchSize) as $rows) {
                $i++;
                $this->rows = [];
                foreach ($rows as $row) {
                    $this->rows[] = $row;
                }
                $this->renderBody($i == $batches);
            }
        }
    }

    /**
     * циклический вывод строк тела отчета
     * @param bool $lastBatch
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function renderBody($lastBatch = false)
    {
        foreach ($this->rows as $row) {
            // идем поэлементно по массиву с результатами запроса
            // если данные по текущему виду животных кончились, выводим итог
            if ($this->currentSpeciesId !== $row['idSpec']
                && $this->currentSpeciesId !== false
                || ($this->currentOrgId !== $row['id_reg_organization']
                    || $this->currentDistId !== $row['id_district']
                    || $this->currentAreaId !== $row['id_area'])
                && $this->currentSpeciesId !== false) {
                $this->_renderTotalRow(
                    'Итого по виду ' . $this->totals['totalPerSpec'],
                    self::STYLE_SPECIESGREY
                );
                $this->currentSpeciesId = false;
                $this->totals['totalPerSpec'] = 0;
            }

            // если данные в текущей организации кончились, выводим итог
            if ($this->currentOrgId !== $row['id_reg_organization']
                && $this->currentOrgId !== false
                || (($this->currentDistId !== $row['id_district']
                    || $this->currentAreaId !== $row['id_area']))
                && $this->currentOrgId !== false) {
                $this->_renderTotalRow(
                    'Итого по организации ' . $this->totals['totalPerOrg'],
                    self::STYLE_ORGGREY
                );
                $this->currentOrgId = false;
                $this->currentSpeciesId = false;
                $this->totals['totalPerOrg'] = 0;
            }

            // если данные в текущем районе кончились, выводим итог
            if ($this->currentDistId !== $row['id_district'] && $this->currentDistId !== false) {
                $this->_renderTotalRow(
                    'Итого по району ' . $this->totals['totalPerDist'],
                    self::STYLE_DISTGREY
                );
                $this->currentSpeciesId = false;
                $this->currentOrgId = false;
                $this->totals['totalPerDist'] = 0;
            }

            // если данные в текущем округе кончились, выводим итог
            if ($this->currentAreaId !== $row['id_area'] && $this->currentAreaId !== false) {
                $this->_renderTotalRow(
                    'Итого по округу ' . $this->totals['totalPerArea'],
                    self::STYLE_AREAGREY
                );
                $this->currentSpeciesId = false;
                $this->currentOrgId = false;
                $this->totals['totalPerArea'] = 0;
            }

            // если начался новый округ, выводим его название
            if ($this->currentAreaId !== $row['id_area']) {
                $this->currentAreaId = $row['id_area'];
                $this->_renderHeaderRow(
                    $row['areaName'] ?? self::NO_AREA,
                    self::STYLE_AREAGREY
                );
            }

            // если начался новый район, выводим его название
            if ($this->currentDistId !== $row['id_district']) {
                $this->currentDistId = $row['id_district'];
                $this->_renderHeaderRow(
                    $row['distName'] ?? self::NO_DISTRICT,
                    self::STYLE_DISTGREY
                );
            }

            // если началась новая орагнизация, выводим ее название
            if ($this->currentOrgId !== $row['id_reg_organization']) {
                $this->currentOrgId = $row['id_reg_organization'];
                $this->_renderHeaderRow(
                    $row['short_name'],
                    self::STYLE_ORGGREY
                );
            }

            // если начался новый вид животных, выводим его название
            if ($this->currentSpeciesId !== $row['idSpec']) {
                $this->currentSpeciesId = $row['idSpec'];
                $this->_renderHeaderRow(
                    $row['specName'],
                    self::STYLE_SPECIESGREY
                );
            }

            $this->_renderRow($row['ownName'], $row);

            // ведем подсчет
            foreach ($this->totals as &$total) {
                $total++;
            }
        }

        if ($lastBatch === true) {
            // Итог по последнему виду животных
            $this->_renderTotalRow(
                'Итого по виду ' . $this->totals['totalPerSpec'],
                self::STYLE_SPECIESGREY
            );
            // Итог по последней организации
            $this->_renderTotalRow(
                'Итого по организации ' . $this->totals['totalPerOrg'],
                self::STYLE_ORGGREY
            );
            // Итог по последнему району
            $this->_renderTotalRow(
                'Итого по району' . $this->totals['totalPerDist'],
                self::STYLE_DISTGREY
            );
            // Итог по последнему округу
            $this->_renderTotalRow(
                'Итого по округу' . $this->totals['totalPerArea'],
                self::STYLE_AREAGREY
            );
            // ВСЕГО
            $this->_renderTotalRow(
                'ВСЕГО ' . $this->totals['total'],
                self::STYLE_TOTALGRAY
            );
        }
    }

    /**
     * @param      $title
     * @param      $report_row
     * @param bool $style
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function _renderRow($title, $report_row, $style = false)
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

        foreach ($report_row as $key => $value) {
            $value = trim($value);
            $value = ltrim($value, '=-+^');
            $report_row[$key] = $value;
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
            null,
            'A' . $this->currentRowNum,
            true
        );

        // Стиль?
        if (!empty($style)) {
            $sheet->getStyleByColumnAndRow(
                1,
                $this->currentRowNum,
                self::COL_COUNT,
                $this->currentRowNum
            )
                ->applyFromArray($style);
        }
        $this->currentRowNum++;
    }

    /**
     * @param      $title
     * @param bool $style
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function _renderTotalRow($title, $style = false)
    {
        $sheet = $this->spreadsheet->getActiveSheet();

        $sheet->fromArray(
            [
                $title,
            ],
            null,
            'A' . $this->currentRowNum,
            true
        );

        // Стиль?
        if (!empty($style)) {
            $sheet->getStyleByColumnAndRow(
                1,
                $this->currentRowNum,
                self::COL_COUNT,
                $this->currentRowNum
            )
                ->applyFromArray($style);
        }
        $this->currentRowNum++;
    }

    /**
     * @param      $title
     * @param bool $style
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function _renderHeaderRow($title, $style = false)
    {
        $sheet = $this->spreadsheet->getActiveSheet();

        $sheet->mergeCells('A' . $this->currentRowNum . ':F' . $this->currentRowNum);

        $sheet->setCellValueByColumnAndRow(
            '1',
            $this->currentRowNum,
            $title
        );

        // Стиль?
        if (!empty($style)) {
            $sheet->getStyleByColumnAndRow(
                1,
                $this->currentRowNum,
                self::COL_COUNT,
                $this->currentRowNum
            )
                ->applyFromArray($style);
        }
        $this->currentRowNum++;
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
            ->setWidth(20);

        $sheet
            ->getColumnDimension('F')
            ->setWidth(20);

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
