<?php

namespace app\modules\adminv\models\excel;

use yii\helpers\ArrayHelper;

/**
 * Детальный отчет по приемам
 *
 * Class FullVisitsVol2ReportExport
 * @package app\modules\adminv\models\excel
 */
class FullVisitsVol2ReportExport extends AbstractReportExport
{
    const STYLE_AREAGREY = 3;
    const STYLE_DISTGREY = 4;
    const STYLE_ORGGREY = 5;

    /**
     * @var string
     */
    protected $filename = 'Детальный отчет по приемам c {from} по {to}';
    /**
     * @var string
     */
    protected $title = 'Детальный отчет по приемам за период c {from} по {to}';
    /**
     * @var string
     */
    protected $maxColumn = 'H';
    /**
     * @var string
     */
    protected $cols = '<cols>'
    . '<col min="1" max="1" width="40" customWidth="true" style="0"/>'
    . '<col min="2" max="2" width="20" customWidth="true" style="0"/>'
    . '<col min="3" max="3" width="20" customWidth="true" style="0"/>'
    . '<col min="4" max="4" width="20" customWidth="true" style="0"/>'
    . '<col min="5" max="5" width="20" customWidth="true" style="0"/>'
    . '<col min="6" max="6" width="20" customWidth="true" style="0"/>'
    . '<col min="7" max="7" width="25" customWidth="true" style="0"/>'
    . '<col min="8" max="8" width="25" customWidth="true" style="0"/>'
    . '</cols>';

    /**
     * @inheritDoc
     */
    protected function renderHead()
    {
        $this->mergeCells = '<mergeCell ref="A1:H1"/>';

        $sst = '<si><t>' . $this->title . '</t></si>';
        $this->writeLn($this->stringFile, $sst);

        $str = '<row r="' . $this->currentRow . '" spans="1:8" customHeight="1" ht="30">'
            . '<c r="A' . $this->currentRow . '" s="1" t="s"><v>0</v></c>'
            . '<c r="B' . $this->currentRow . '" s="1"/>'
            . '<c r="C' . $this->currentRow . '" s="1"/>'
            . '<c r="D' . $this->currentRow . '" s="1"/>'
            . '<c r="E' . $this->currentRow . '" s="1"/>'
            . '<c r="F' . $this->currentRow . '" s="1"/>'
            . '<c r="G' . $this->currentRow . '" s="1"/>'
            . '<c r="H' . $this->currentRow . '" s="1"/>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;

        $str = '<row r="' . $this->currentRow . '" spans="1:8" customHeight="1" ht="45">'
            . '<c r="A' . $this->currentRow . '" s="2"/>'
            . '<c r="B' . $this->currentRow . '" s="2" t="str"><v>Всего</v></c>'
            . '<c r="C' . $this->currentRow . '" s="2" t="str"><v>Завершено</v></c>'
            . '<c r="D' . $this->currentRow . '" s="2" t="str"><v>Отменено организацией</v></c>'
            . '<c r="E' . $this->currentRow . '" s="2" t="str"><v>Отменено владельцем</v></c>'
            . '<c r="F' . $this->currentRow . '" s="2" t="str"><v>Перенесено</v></c>'
            . '<c r="G' . $this->currentRow . '" s="2" t="str"><v>Перенесено организацией (только для mos.ru)</v></c>'
            . '<c r="H' . $this->currentRow . '" s="2" t="str"><v>Перенесено владельцем (только для mos.ru)</v></c>'
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
        $currentDistId = false;
        $currentOrgId = false;
        $totals = [
            'totalVisitsPerOrg' => 0,
            'totalVisitsPerDist' => 0,
            'totalVisitsPerArea' => 0,
            'totalVisits' => 0,

            'totalFinishedPerOrg' => 0,
            'totalFinishedPerDist' => 0,
            'totalFinishedPerArea' => 0,
            'totalFinished' => 0,

            'totalCancelledByOrgPerOrg' => 0,
            'totalCancelledByOrgPerDist' => 0,
            'totalCancelledByOrgPerArea' => 0,
            'totalCancelledByOrg' => 0,

            'totalCancelledByOwnerPerOrg' => 0,
            'totalCancelledByOwnerPerDist' => 0,
            'totalCancelledByOwnerPerArea' => 0,
            'totalCancelledByOwner' => 0,

            'totalTransferredPerOrg' => 0,
            'totalTransferredPerDist' => 0,
            'totalTransferredPerArea' => 0,
            'totalTransferred' => 0,

            'totalTransferredByOrgPerOrg' => 0,
            'totalTransferredByOrgPerDist' => 0,
            'totalTransferredByOrgPerArea' => 0,
            'totalTransferredByOrg' => 0,

            'totalTransferredByOwnerPerOrg' => 0,
            'totalTransferredByOwnerPerDist' => 0,
            'totalTransferredByOwnerPerArea' => 0,
            'totalTransferredByOwner' => 0,
        ];

        foreach ($this->data as $row) {
            // если данные в текущей орагнизации кончились, выводим итог
            if ($currentOrgId != $row['id'] && $currentOrgId != false) {
                $this->renderTotalRow(
                    'Итого по организации',
                    $totals['totalVisitsPerOrg'],
                    $totals['totalFinishedPerOrg'],
                    $totals['totalCancelledByOrgPerOrg'],
                    $totals['totalCancelledByOwnerPerOrg'],
                    $totals['totalTransferredPerOrg'],
                    $totals['totalTransferredByOrgPerOrg'],
                    $totals['totalTransferredByOwnerPerOrg'],
                    self::STYLE_ORGGREY
                );
                $currentOrgId = false;
                $totals['totalVisitsPerOrg'] = 0;
                $totals['totalFinishedPerOrg'] = 0;
                $totals['totalCancelledByOrgPerOrg'] = 0;
                $totals['totalCancelledByOwnerPerOrg'] = 0;
                $totals['totalTransferredPerOrg'] = 0;
                $totals['totalTransferredByOrgPerOrg'] = 0;
                $totals['totalTransferredByOwnerPerOrg'] = 0;
            }

            // если данные в текущем районе кончились, выводим итог
            if ($currentDistId !== $row['id_district'] && $currentDistId !== false) {
                $this->renderTotalRow(
                    'Итого по району',
                    $totals['totalVisitsPerDist'],
                    $totals['totalFinishedPerDist'],
                    $totals['totalCancelledByOrgPerDist'],
                    $totals['totalCancelledByOwnerPerDist'],
                    $totals['totalTransferredPerDist'],
                    $totals['totalTransferredByOrgPerDist'],
                    $totals['totalTransferredByOwnerPerDist'],
                    self::STYLE_DISTGREY
                );
                $currentDistId = false;
                $totals['totalVisitsPerDist'] = 0;
                $totals['totalFinishedPerDist'] = 0;
                $totals['totalCancelledByOrgPerDist'] = 0;
                $totals['totalCancelledByOwnerPerDist'] = 0;
                $totals['totalTransferredPerDist'] = 0;
                $totals['totalTransferredByOrgPerDist'] = 0;
                $totals['totalTransferredByOwnerPerDist'] = 0;
            }

            // если данные в текущем округе кончились, выводим итог
            if ($currentAreaId !== $row['id_area'] && $currentAreaId !== false) {
                $this->renderTotalRow(
                    'Итого по округу',
                    $totals['totalVisitsPerArea'],
                    $totals['totalFinishedPerArea'],
                    $totals['totalCancelledByOrgPerArea'],
                    $totals['totalCancelledByOwnerPerArea'],
                    $totals['totalTransferredPerArea'],
                    $totals['totalTransferredByOrgPerArea'],
                    $totals['totalTransferredByOwnerPerArea'],
                    self::STYLE_AREAGREY
                );
                $currentAreaId = false;
                $totals['totalVisitsPerArea'] = 0;
                $totals['totalFinishedPerArea'] = 0;
                $totals['totalCancelledByOrgPerArea'] = 0;
                $totals['totalCancelledByOwnerPerArea'] = 0;
                $totals['totalTransferredPerArea'] = 0;
                $totals['totalTransferredByOrgPerArea'] = 0;
                $totals['totalTransferredByOwnerPerArea'] = 0;
            }

            // если начался новый округ, выводим его название
            if ($currentAreaId !== $row['id_area']) {
                $currentAreaId = $row['id_area'];
                $this->renderHeaderRow(
                    $row['area_name'] ?? self::NO_AREA,
                    self::STYLE_AREAGREY
                );
            }

            // если начался новый район, выводим его название
            if ($currentDistId !== $row['id_district']) {
                $currentDistId = $row['id_district'];
                $this->renderHeaderRow(
                    $row['dist_name'] ?? self::NO_DISTRICT,
                    self::STYLE_DISTGREY
                );
            }

            // если началась новая организация, выводим ее название
            if ($currentOrgId !== $row['id']) {
                $currentOrgId = $row['id'];
                $this->renderHeaderRow(
                    $row['short_name'],
                    self::STYLE_ORGGREY
                );
            }

            $this->renderRow($row);

            // ведем подсчет
            $totals['totalVisitsPerOrg'] += $row['total_visits'];
            $totals['totalFinishedPerOrg'] += $row['total_finished'];
            $totals['totalCancelledByOrgPerOrg'] += $row['cancelled_by_clinic'];
            $totals['totalCancelledByOwnerPerOrg'] += $row['cancelled_by_owner'];
            $totals['totalTransferredPerOrg'] += $row['transferred_not_mosru'];
            $totals['totalTransferredByOrgPerOrg'] += $row['transferred_by_clinic_mosru'];
            $totals['totalTransferredByOwnerPerOrg'] += $row['transferred_by_owner_mosru'];

            $totals['totalVisitsPerDist'] += $row['total_visits'];
            $totals['totalFinishedPerDist'] += $row['total_finished'];
            $totals['totalCancelledByOrgPerDist'] += $row['cancelled_by_clinic'];
            $totals['totalCancelledByOwnerPerDist'] += $row['cancelled_by_owner'];
            $totals['totalTransferredPerDist'] += $row['transferred_not_mosru'];
            $totals['totalTransferredByOrgPerDist'] += $row['transferred_by_clinic_mosru'];
            $totals['totalTransferredByOwnerPerDist'] += $row['transferred_by_owner_mosru'];

            $totals['totalVisitsPerArea'] += $row['total_visits'];
            $totals['totalFinishedPerArea'] += $row['total_finished'];
            $totals['totalCancelledByOrgPerArea'] += $row['cancelled_by_clinic'];
            $totals['totalCancelledByOwnerPerArea'] += $row['cancelled_by_owner'];
            $totals['totalTransferredPerArea'] += $row['transferred_not_mosru'];
            $totals['totalTransferredByOrgPerArea'] += $row['transferred_by_clinic_mosru'];
            $totals['totalTransferredByOwnerPerArea'] += $row['transferred_by_owner_mosru'];

            $totals['totalVisits'] += $row['total_visits'];
            $totals['totalFinished'] += $row['total_finished'];
            $totals['totalCancelledByOrg'] += $row['cancelled_by_clinic'];
            $totals['totalCancelledByOwner'] += $row['cancelled_by_owner'];
            $totals['totalTransferred'] += $row['transferred_not_mosru'];
            $totals['totalTransferredByOrg'] += $row['transferred_by_clinic_mosru'];
            $totals['totalTransferredByOwner'] += $row['transferred_by_owner_mosru'];
        }

        /*
         * Итог по последней организации
         */
        $this->renderTotalRow(
            'Итого по организации',
            $totals['totalVisitsPerOrg'],
            $totals['totalFinishedPerOrg'],
            $totals['totalCancelledByOrgPerOrg'],
            $totals['totalCancelledByOwnerPerOrg'],
            $totals['totalTransferredPerOrg'],
            $totals['totalTransferredByOrgPerOrg'],
            $totals['totalTransferredByOwnerPerOrg'],
            self::STYLE_ORGGREY
        );

        /*
         * Итог по последнему району
         */
        $this->renderTotalRow(
            'Итого по району',
            $totals['totalVisitsPerDist'],
            $totals['totalFinishedPerDist'],
            $totals['totalCancelledByOrgPerDist'],
            $totals['totalCancelledByOwnerPerDist'],
            $totals['totalTransferredPerDist'],
            $totals['totalTransferredByOrgPerDist'],
            $totals['totalTransferredByOwnerPerDist'],
            self::STYLE_DISTGREY
        );

        /*
         * Итог по последнему округу
         */
        $this->renderTotalRow(
            'Итого по округу',
            $totals['totalVisitsPerArea'],
            $totals['totalFinishedPerArea'],
            $totals['totalCancelledByOrgPerArea'],
            $totals['totalCancelledByOwnerPerArea'],
            $totals['totalTransferredPerArea'],
            $totals['totalTransferredByOrgPerArea'],
            $totals['totalTransferredByOwnerPerArea'],
            self::STYLE_AREAGREY
        );

        /*
         * ВСЕГО
         */
        $this->renderTotalRow(
            'ВСЕГО',
            $totals['totalVisits'],
            $totals['totalFinished'],
            $totals['totalCancelledByOrg'],
            $totals['totalCancelledByOwner'],
            $totals['totalTransferred'],
            $totals['totalTransferredByOrg'],
            $totals['totalTransferredByOwner'],
            self::STYLE_AREAGREY
        );
    }

    /**
     * @param string $title
     * @param int    $style
     */
    private function renderHeaderRow($title, $style)
    {
        $this->mergeCells .= '<mergeCell ref="A' . $this->currentRow . ':' . $this->maxColumn . $this->currentRow . '"/>';

        $title = $this->cleanupCellValue($title);

        $str = '<row r="' . $this->currentRow . '" spans="1:8">'
            . '<c r="A' . $this->currentRow . '" s="' . $style . '" t="str"><v>' . $title . '</v></c>'
            . '<c r="B' . $this->currentRow . '" s="' . $style . '"/>'
            . '<c r="C' . $this->currentRow . '" s="' . $style . '"/>'
            . '<c r="D' . $this->currentRow . '" s="' . $style . '"/>'
            . '<c r="E' . $this->currentRow . '" s="' . $style . '"/>'
            . '<c r="F' . $this->currentRow . '" s="' . $style . '"/>'
            . '<c r="G' . $this->currentRow . '" s="' . $style . '"/>'
            . '<c r="H' . $this->currentRow . '" s="' . $style . '"/>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;
    }

    /**
     * @param string $title
     * @param int    $totalVisits
     * @param int    $totalFinished
     * @param int    $totalCancelledByOrg
     * @param int    $totalCancelledByOwner
     * @param int    $totalTransferred
     * @param int    $totalTransferredByOrg
     * @param int    $totalTransferredByOwner
     * @param int    $style
     */
    private function renderTotalRow(
        $title,
        $totalVisits,
        $totalFinished,
        $totalCancelledByOrg,
        $totalCancelledByOwner,
        $totalTransferred,
        $totalTransferredByOrg,
        $totalTransferredByOwner,
        $style
    )
    {
        $title = $this->cleanupCellValue($title);

        $str = '<row r="' . $this->currentRow . '" spans="1:8">'
            . '<c r="A' . $this->currentRow . '" s="' . $style . '" t="str"><v>' . $title . '</v></c>'
            . '<c r="B' . $this->currentRow . '" s="' . $style . '"><v>' . $totalVisits . '</v></c>'
            . '<c r="C' . $this->currentRow . '" s="' . $style . '"><v>' . $totalFinished . '</v></c>'
            . '<c r="D' . $this->currentRow . '" s="' . $style . '"><v>' . $totalCancelledByOrg . '</v></c>'
            . '<c r="E' . $this->currentRow . '" s="' . $style . '"><v>' . $totalCancelledByOwner . '</v></c>'
            . '<c r="F' . $this->currentRow . '" s="' . $style . '"><v>' . $totalTransferred . '</v></c>'
            . '<c r="G' . $this->currentRow . '" s="' . $style . '"><v>' . $totalTransferredByOrg . '</v></c>'
            . '<c r="H' . $this->currentRow . '" s="' . $style . '"><v>' . $totalTransferredByOwner . '</v></c>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;
    }

    /**
     * @param array $row
     */
    private function renderRow($row)
    {
        $title = $this->cleanupCellValue($row['spec_name']);

        $totalVisits = ArrayHelper::getValue($row, 'total_visits', 0);
        $totalFinished = ArrayHelper::getValue($row, 'total_finished', 0);
        $totalCancelledByOrg = ArrayHelper::getValue($row, 'cancelled_by_clinic', 0);
        $totalCancelledByOwner = ArrayHelper::getValue($row, 'cancelled_by_owner', 0);
        $totalTransferred = ArrayHelper::getValue($row, 'transferred_not_mosru', 0);
        $totalTransferredByOrg = ArrayHelper::getValue($row, 'transferred_by_clinic_mosru', 0);
        $totalTransferredByOwner = ArrayHelper::getValue($row, 'transferred_by_owner_mosru', 0);

        $str = '<row r="' . $this->currentRow . '" spans="1:8">'
            . '<c r="A' . $this->currentRow . '" t="str"><v>' . $title . '</v></c>'
            . '<c r="B' . $this->currentRow . '"><v>' . $totalVisits . '</v></c>'
            . '<c r="C' . $this->currentRow . '"><v>' . $totalFinished . '</v></c>'
            . '<c r="D' . $this->currentRow . '"><v>' . $totalCancelledByOrg . '</v></c>'
            . '<c r="E' . $this->currentRow . '"><v>' . $totalCancelledByOwner . '</v></c>'
            . '<c r="F' . $this->currentRow . '"><v>' . $totalTransferred . '</v></c>'
            . '<c r="G' . $this->currentRow . '"><v>' . $totalTransferredByOrg . '</v></c>'
            . '<c r="H' . $this->currentRow . '"><v>' . $totalTransferredByOwner . '</v></c>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;
    }
}
