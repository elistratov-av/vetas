<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 22.07.19
 * Time: 15:48
 */

namespace app\modules\adminv\models\export;


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class RegPetsReportExport
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
                'argb' => '808080',
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
            'horizontal' => Alignment::HORIZONTAL_LEFT,
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
        $currentSpeciesId = false;
        $totals = [
            'totalPerSpec' => 0,
            'totalPerOrg' => 0,
            'total' => 0,
        ];

        foreach ($this->report as $report_row) { // идем поэлементно по массиву с результатами запроса
            // если данные по текущему виду животных кончились, выводим итог
            if(($currentSpeciesId != $report_row['id_species']
                && $currentSpeciesId != false
                ||  (($currentOrgId != $report_row['id_reg_organization']))
                && $currentSpeciesId != false)) {
                $this->_renderTotalRow(
                    'Итого по виду ' . $totals['totalPerSpec'],
                    self::STYLE_SPECIESGREY
                );
                $currentSpeciesId = false;
                $totals['totalPerSpec'] = 0;
            }

            // если данные в текущей организации кончились, выводим итог
            if($currentOrgId != $report_row['id_reg_organization']
                && $currentOrgId != false) {
                $this->_renderTotalRow(
                    'Итого по организации ' . $totals['totalPerOrg'],
                    self::STYLE_ORGGREY
                );
                $currentOrgId = false;
                $currentSpeciesId = false;
                $totals['totalPerOrg'] = 0;
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
            if($currentSpeciesId !== $report_row['id_species']) {
                $currentSpeciesId = $report_row['id_species'];
                $this->_renderHeaderRow(
                    $report_row['spec_name'],
                    self::STYLE_SPECIESGREY
                );
            }

            $this->_renderRow($report_row['pet_name'], $report_row);

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
    protected function _renderRow($title, $report_row, $style = self::STYLE_INFOROW)
    {
        $sheet = $this->spreadsheet->getActiveSheet();

        // коэффициент для автовысоты ячейки, в которой выводится адрес владельца.
        // Предположим, что стандартной высоты хватит на 30 символов, иначе увеличиваем
        $coef = intdiv(mb_strlen($report_row['full_address']), 30) + 1;

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
                $report_row['ident_name'],
                $report_row['identification_code'],
                $report_row['fullname'],
                $report_row['full_address'],
                $report_row['contact_name'],
                $report_row['reg_date'],
                $report_row['number']
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

        $sheet->mergeCells('A' . $this->currentRowNum . ':H' . $this->currentRowNum);

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

        $sheet->mergeCells('A' . $this->currentRowNum . ':H' . $this->currentRowNum);

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
            ->getColumnDimension('A');

        $sheet
            ->getStyle('A:A')
            ->getAlignment()->setWrapText(true); // Автоперенос

        $sheet
            ->getStyle('D:E')
            ->getAlignment()->setWrapText(true); // Автоперенос

        $sheet
            ->getRowDimension('1')
            ->setRowHeight(30);

        $message = 'Отчет о выданных регистрационных удостоверениях с ' . $from . ' по ' . $to;
        $sheet->setCellValueByColumnAndRow(
            '1',
            $this->currentRowNum,
            $message
        );

        $sheet
            ->getColumnDimension('A')
            ->setWidth(20);

        $sheet
            ->getColumnDimension('B')
            ->setWidth(21);

        $sheet
            ->getColumnDimension('C')
            ->setWidth(20);

        $sheet
            ->getColumnDimension('D')
            ->setWidth(30);

        $sheet
            ->getColumnDimension('E')
            ->setWidth(40);

        $sheet
            ->getColumnDimension('F')
            ->setWidth(25);

        $sheet
            ->getColumnDimension('G')
            ->setWidth(15);

        $sheet
            ->getColumnDimension('H')
            ->setWidth(20);

        $sheet
            ->getRowDimension('2')
            ->setRowHeight(50); // Высота

        $sheet
            ->getStyle('A2:H2')
            ->getAlignment()->setWrapText(true); // Автоперенос

        $sheet
            ->getStyle('A1:H2')
            ->applyFromArray(self::STYLE_HEADER);

        $sheet
            ->setCellValue('A2', 'Кличка');

        $sheet
            ->setCellValue('B2', 'Тип метки (основной идентификатор животного)');

        $sheet
            ->setCellValue('C2', 'Значение метки');

        $sheet
            ->setCellValue('D2', 'Владелец (ФЛ, ЮЛ)');

        $sheet
            ->setCellValue('E2', 'Адрес владельца (ФЛ, ЮЛ)');

        $sheet
            ->setCellValue('F2', 'Телефон владельца (ФЛ, ЮЛ)');

        $sheet
            ->setCellValue('G2', 'Дата регистрации');

        $sheet
            ->setCellValue('H2', 'Номер регистрационного удостоверения');

        // Передвигаем свой указатель на строку ниже
        $this->currentRowNum = 3;
    }
}
