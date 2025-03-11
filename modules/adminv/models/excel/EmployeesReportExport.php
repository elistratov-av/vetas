<?php

namespace app\modules\adminv\models\excel;

use yii\helpers\ArrayHelper;

/**
 * Отчет по работе сотрудников
 *
 * Class EmployeesReportExport
 * @package app\modules\adminv\models\excel
 */
class EmployeesReportExport extends AbstractReportExport
{
    const STYLE_LIGHTGREY = 8;
    const STYLE_MIDGREY = 5;
    const STYLE_DARKGRAY = 11;

    /**
     * @var string
     */
    protected $filename = 'Отчет по работе сотрудников c {from} по {to}';
    /**
     * @var string
     */
    protected $maxColumn = 'E';
    /**
     * @var string
     */
    protected $cols = '<cols>'
    . '<col min="1" max="1" width="55.272217" bestFit="true" customWidth="true" style="3"/>'
    . '<col min="2" max="2" width="15" customWidth="true" style="0"/>'
    . '<col min="3" max="3" width="15" customWidth="true" style="0"/>'
    . '<col min="4" max="4" width="15" customWidth="true" style="0"/>'
    . '<col min="5" max="5" width="16" customWidth="true" style="0"/>'
    . '</cols>';

    /**
     * @inheritDoc
     */
    protected function renderHead()
    {
        $this->mergeCells = '<mergeCell ref="A1:E1"/>';

        $sst = '<si><t>' . $this->title . '</t></si>';
        $this->writeLn($this->stringFile, $sst);

        $str = '<row r="' . $this->currentRow . '" spans="1:5" customHeight="1" ht="30">'
            . '<c r="A' . $this->currentRow . '" s="1" t="s"><v>0</v></c>'
            . '<c r="B' . $this->currentRow . '" s="1"/>'
            . '<c r="C' . $this->currentRow . '" s="1"/>'
            . '<c r="D' . $this->currentRow . '" s="1"/>'
            . '<c r="E' . $this->currentRow . '" s="1"/>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);

        $this->currentRow++;
        $str = '<row r="' . $this->currentRow . '" spans="1:5" customHeight="1" ht="30">'
            . '<c r="A' . $this->currentRow . '" s="2" t="str"><v>ФИО врача</v></c>'
            . '<c r="B' . $this->currentRow . '" s="2" t="str"><v>Приемы (количество)</v></c>'
            . '<c r="C' . $this->currentRow . '" s="2" t="str"><v>Услуги (количество)</v></c>'
            . '<c r="D' . $this->currentRow . '" s="2" t="str"><v>Стоимость приемов</v></c>'
            . '<c r="E' . $this->currentRow . '" s="2" t="str"><v>Безвозмездные услуги</v></c>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;
    }

    /**
     * @inheritDoc
     */
    protected function renderBody()
    {
        $currentAreaId = false;
        $currentOrgId = false;
        $totals = [
            'totalVisitsPerOrg' => 0,
            'totalServicesPerOrg' => 0,
            'totalAmountPerOrg' => 0,
            'totalFreeServicesPerOrg' => 0,
            'totalVisitsPerArea' => 0,
            'totalServicesPerArea' => 0,
            'totalAmountPerArea' => 0,
            'totalFreeServicesPerArea' => 0,
            'totalVisits' => 0,
            'totalServices' => 0,
            'totalAmount' => 0,
            'totalFreeServices' => 0,
        ];

        foreach ($this->data as $row) {
            // если данные в текущей организации кончились, выводим итог
            if ($currentOrgId !== $row['id_organization'] && $currentOrgId !== false) {
                $this->renderTotalRow(
                    'Итого по организации',
                    $totals['totalVisitsPerOrg'],
                    $totals['totalServicesPerOrg'],
                    $totals['totalAmountPerOrg'],
                    $totals['totalFreeServicesPerOrg'],
                    self::STYLE_LIGHTGREY
                );
                $currentOrgId = false;
                $totals['totalVisitsPerOrg'] = 0;
                $totals['totalServicesPerOrg'] = 0;
                $totals['totalAmountPerOrg'] = 0;
                $totals['totalFreeServicesPerOrg'] = 0;
            }

            // если данные в текущем округе кончились, выводим итог
            if ($currentAreaId !== $row['id_area'] && $currentAreaId !== false) {
                $this->renderTotalRow(
                    'Итого по округу',
                    $totals['totalVisitsPerArea'],
                    $totals['totalServicesPerArea'],
                    $totals['totalAmountPerArea'],
                    $totals['totalFreeServicesPerArea'],
                    self::STYLE_MIDGREY
                );
                $currentAreaId = false;
                $totals['totalVisitsPerArea'] = 0;
                $totals['totalServicesPerArea'] = 0;
                $totals['totalAmountPerArea'] = 0;
                $totals['totalFreeServicesPerArea'] = 0;
            }

            // если начался новый округ, выводим его название
            if ($currentAreaId !== $row['id_area']) {
                $currentAreaId = $row['id_area'];
                $this->renderHeaderRow(
                    $row['name'] ?? self::NO_AREA,
                    self::STYLE_MIDGREY
                );
            }

            // если началась новая организация, выводим ее название
            if ($currentOrgId !== $row['id_organization']) {
                $currentOrgId = $row['id_organization'];
                $this->renderHeaderRow(
                    $row['short_name'],
                    self::STYLE_LIGHTGREY
                );
            }

            $this->renderRow($row);

            // ведем подсчет по орг
            $totals['totalVisitsPerOrg'] += $row['total_visits'];
            $totals['totalServicesPerOrg'] += $row['total_services'];
            $totals['totalAmountPerOrg'] += $row['total_amount'];
            $totals['totalFreeServicesPerOrg'] += $row['total_free_services'];

            // ведем подсчет по округу
            $totals['totalVisitsPerArea'] += $row['total_visits'];
            $totals['totalServicesPerArea'] += $row['total_services'];
            $totals['totalAmountPerArea'] += $row['total_amount'];
            $totals['totalFreeServicesPerArea'] += $row['total_free_services'];

            // ведем общий подсчет
            $totals['totalVisits'] += $row['total_visits'];
            $totals['totalServices'] += $row['total_services'];
            $totals['totalAmount'] += $row['total_amount'];
            $totals['totalFreeServices'] += $row['total_free_services'];
        }

        /*
         * Итог по последней организации
         */
        $this->renderTotalRow(
            'Итого по организации',
            $totals['totalVisitsPerOrg'],
            $totals['totalServicesPerOrg'],
            $totals['totalAmountPerOrg'],
            $totals['totalFreeServicesPerOrg'],
            self::STYLE_LIGHTGREY
        );

        /*
         * Итог по последнему округу
         */
        $this->renderTotalRow(
            'Итого по округу',
            $totals['totalVisitsPerArea'],
            $totals['totalServicesPerArea'],
            $totals['totalAmountPerArea'],
            $totals['totalFreeServicesPerArea'],
            self::STYLE_MIDGREY
        );

        /*
         * ВСЕГО
         */
        $this->renderTotalRow(
            'ВСЕГО',
            $totals['totalVisits'],
            $totals['totalServices'],
            $totals['totalAmount'],
            $totals['totalFreeServices'],
            self::STYLE_DARKGRAY
        );
    }

    /**
     * @param string $title
     * @param int    $style
     */
    private function renderHeaderRow($title, $style)
    {
        $title = $this->cleanupCellValue($title);

        $str = '<row r="' . $this->currentRow . '" spans="1:5">'
            . '<c r="A' . $this->currentRow . '" s="' . $style . '" t="str"><v>' . $title . '</v></c>'
            . '<c r="B' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="C' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="D' . $this->currentRow . '" s="' . ($style + 2) . '"/>'
            . '<c r="E' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;
    }

    /**
     * @param string $title
     * @param int    $totalVisits
     * @param int    $totalServices
     * @param int    $totalAmount
     * @param int    $totalFreeServices
     * @param int    $style
     */
    private function renderTotalRow($title, $totalVisits, $totalServices, $totalAmount, $totalFreeServices, $style)
    {
        $title = $this->cleanupCellValue($title);

        $str = '<row r="' . $this->currentRow . '" spans="1:5">'
            . '<c r="A' . $this->currentRow . '" s="' . $style . '" t="str"><v>' . $title . '</v></c>'
            . '<c r="B' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalVisits . '</v></c>'
            . '<c r="C' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalServices . '</v></c>'
            . '<c r="D' . $this->currentRow . '" s="' . ($style + 2) . '"><v>' . $totalAmount . '</v></c>'
            . '<c r="E' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalFreeServices . '</v></c>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;
    }

    /**
     * @param array $row
     */
    private function renderRow($row)
    {
        $title = $this->cleanupCellValue(ArrayHelper::getValue($row, 'fullname', ''));

        $totalVisits = ArrayHelper::getValue($row, 'total_visits', 0);
        $totalServices = ArrayHelper::getValue($row, 'total_services', 0);
        $totalAmount = ArrayHelper::getValue($row, 'total_amount', 0);
        $totalFreeServices = ArrayHelper::getValue($row, 'total_free_services', 0);

        $str = '<row r="' . $this->currentRow . '" spans="1:5">'
            . '<c r="A' . $this->currentRow . '" s="3" t="str"><v>' . $title . '</v></c>'
            . '<c r="B' . $this->currentRow . '"><v>' . $totalVisits . '</v></c>'
            . '<c r="C' . $this->currentRow . '"><v>' . $totalServices . '</v></c>'
            . '<c r="D' . $this->currentRow . '" s="4"><v>' . $totalAmount . '</v></c>'
            . '<c r="E' . $this->currentRow . '"><v>' . $totalFreeServices . '</v></c>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;
    }
}
