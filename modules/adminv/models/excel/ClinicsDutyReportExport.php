<?php

namespace app\modules\adminv\models\excel;

use yii\helpers\ArrayHelper;

/**
 * Отчет о нагрузке на ветеринарные учреждения и службы
 *
 * Class ClinicsDutyReportExport
 * @package app\modules\adminv\models\excel
 */
class ClinicsDutyReportExport extends AbstractReportExport
{
    const STYLE_LIGHTGREY = 6;
    const STYLE_MIDGREY = 4;
    const STYLE_DARKGRAY = 8;

    /**
     * @var string
     */
    protected $filename = 'Отчет о нагрузке на ветеринарные учреждения и службы c {from} по {to}';
    /**
     * @var string
     */
    protected $maxColumn = 'H';
    /**
     * @var string
     */
    protected $cols = '<cols>'
    . '<col min="1" max="1" width="55.272217" bestFit="true" customWidth="true" style="3"/>'
    . '<col min="2" max="2" width="15" customWidth="true" style="0"/>'
    . '<col min="3" max="3" width="15" customWidth="true" style="0"/>'
    . '<col min="4" max="4" width="15" customWidth="true" style="0"/>'
    . '<col min="5" max="5" width="15" customWidth="true" style="0"/>'
    . '<col min="6" max="6" width="15" customWidth="true" style="0"/>'
    . '<col min="7" max="7" width="15" customWidth="true" style="0"/>'
    . '<col min="8" max="8" width="15" customWidth="true" style="0"/>'
    . '</cols>';

    /**
     * @inheritDoc
     */
    protected function renderHead()
    {
        $this->mergeCells = '<mergeCell ref="A1:H1"/>'
            . '<mergeCell ref="B2:F2"/>';

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

        $str = '<row r="' . $this->currentRow . '" spans="1:8" customHeight="1" ht="30">'
            . '<c r="A' . $this->currentRow . '" s="2"/>'
            . '<c r="B' . $this->currentRow . '" s="2" t="str"><v>Принято обращений (записей на прием)</v></c>'
            . '<c r="C' . $this->currentRow . '" s="2"/>'
            . '<c r="D' . $this->currentRow . '" s="2"/>'
            . '<c r="E' . $this->currentRow . '" s="2"/>'
            . '<c r="F' . $this->currentRow . '" s="2"/>'
            . '<c r="G' . $this->currentRow . '" s="2" t="str"><v>Проведено приемов</v></c>'
            . '<c r="H' . $this->currentRow . '" s="2" t="str"><v>Оказано услуг</v></c>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;

        $str = '<row r="' . $this->currentRow . '" spans="1:8" customHeight="1" ht="30">'
            . '<c r="A' . $this->currentRow . '" s="2" t="str"><v>Организация</v></c>'
            . '<c r="B' . $this->currentRow . '" s="2" t="str"><v>Записей всего</v></c>'
            . '<c r="C' . $this->currentRow . '" s="2" t="str"><v>Mos.ru</v></c>'
            . '<c r="D' . $this->currentRow . '" s="2" t="str"><v>Телефон</v></c>'
            . '<c r="E' . $this->currentRow . '" s="2" t="str"><v>Живая очередь</v></c>'
            . '<c r="F' . $this->currentRow . '" s="2" t="str"><v>Направление</v></c>'
            . '<c r="G' . $this->currentRow . '" s="2"/>'
            . '<c r="H' . $this->currentRow . '" s="2"/>'
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
        $totals = [
            'totalMosruPerDist' => 0,
            'totalMosruPerArea' => 0,
            'totalMosru' => 0,
            'totalPhonePerDist' => 0,
            'totalPhonePerArea' => 0,
            'totalPhone' => 0,
            'totalLiveQueuePerDist' => 0,
            'totalLiveQueuePerArea' => 0,
            'totalLiveQueue' => 0,
            'totalAppointmentPerDist' => 0,
            'totalAppointmentPerArea' => 0,
            'totalAppointment' => 0,
            'totalVisitsPerDist' => 0,
            'totalVisitsPerArea' => 0,
            'totalVisits' => 0,
            'totalServicesPerDist' => 0,
            'totalServicesPerArea' => 0,
            'totalServices' => 0,
        ];


        foreach ($this->data as $row) {
            // если данные в текущем районе кончились, выводим итог
            if ($currentDistId !== $row['id_district'] && $currentDistId !== false) {
                $this->renderTotalRow(
                    'Итого по району',
                    $totals['totalMosruPerDist'] + $totals['totalPhonePerDist'] + $totals['totalLiveQueuePerDist'] + $totals['totalAppointmentPerDist'],
                    $totals['totalMosruPerDist'],
                    $totals['totalPhonePerDist'],
                    $totals['totalLiveQueuePerDist'],
                    $totals['totalAppointmentPerDist'],
                    $totals['totalVisitsPerDist'],
                    $totals['totalServicesPerDist'],
                    self::STYLE_LIGHTGREY
                );
                $currentDistId = false;
                $totals['totalMosruPerDist'] = 0;
                $totals['totalPhonePerDist'] = 0;
                $totals['totalLiveQueuePerDist'] = 0;
                $totals['totalAppointmentPerDist'] = 0;
                $totals['totalVisitsPerDist'] = 0;
                $totals['totalServicesPerDist'] = 0;
            }

            // если данные в текущем округе кончились, выводим итог
            if ($currentAreaId !== $row['id_area'] && $currentAreaId !== false) {
                $this->renderTotalRow(
                    'Итого по округу',
                    $totals['totalMosruPerArea'] + $totals['totalPhonePerArea'] + $totals['totalLiveQueuePerArea'] + $totals['totalAppointmentPerArea'],
                    $totals['totalMosruPerArea'],
                    $totals['totalPhonePerArea'],
                    $totals['totalLiveQueuePerArea'],
                    $totals['totalAppointmentPerArea'],
                    $totals['totalVisitsPerArea'],
                    $totals['totalServicesPerArea'],
                    self::STYLE_MIDGREY
                );
                $currentAreaId = false;
                $totals['totalMosruPerArea'] = 0;
                $totals['totalPhonePerArea'] = 0;
                $totals['totalLiveQueuePerArea'] = 0;
                $totals['totalAppointmentPerArea'] = 0;
                $totals['totalVisitsPerArea'] = 0;
                $totals['totalServicesPerArea'] = 0;
            }

            // если начался новый округ, выводим его название
            if ($currentAreaId !== $row['id_area']) {
                $currentAreaId = $row['id_area'];
                $this->renderHeaderRow(
                    $row['area_name'] ?? self::NO_AREA,
                    self::STYLE_MIDGREY
                );
            }

            // если начался новый район, выводим его название
            if ($currentDistId !== $row['id_district']) {
                $currentDistId = $row['id_district'];
                $this->renderHeaderRow(
                    $row['dist_name'] ?? self::NO_DISTRICT,
                    self::STYLE_LIGHTGREY
                );
            }

            $this->renderRow($row);

            // ведем подсчет по району
            $totals['totalMosruPerDist'] += $row['mosruVisitsQuery'];
            $totals['totalPhonePerDist'] += $row['phoneVisitsQuery'];
            $totals['totalLiveQueuePerDist'] += $row['liveQueueVisitsQuery'];
            $totals['totalAppointmentPerDist'] += $row['appointmentVisitsQuery'];
            $totals['totalVisitsPerDist'] += $row['totalVisitsQuery'];
            $totals['totalServicesPerDist'] += $row['servicesCounter'];

            // ведем подсчет по округу
            $totals['totalMosruPerArea'] += $row['mosruVisitsQuery'];
            $totals['totalPhonePerArea'] += $row['phoneVisitsQuery'];
            $totals['totalLiveQueuePerArea'] += $row['liveQueueVisitsQuery'];
            $totals['totalAppointmentPerArea'] += $row['appointmentVisitsQuery'];
            $totals['totalVisitsPerArea'] += $row['totalVisitsQuery'];
            $totals['totalServicesPerArea'] += $row['servicesCounter'];

            // ведем общий подсчет
            $totals['totalMosru'] += $row['mosruVisitsQuery'];
            $totals['totalPhone'] += $row['phoneVisitsQuery'];
            $totals['totalLiveQueue'] += $row['liveQueueVisitsQuery'];
            $totals['totalAppointment'] += $row['appointmentVisitsQuery'];
            $totals['totalVisits'] += $row['totalVisitsQuery'];
            $totals['totalServices'] += $row['servicesCounter'];
        }

        /*
         * Итог по последнему району
         */
        $this->renderTotalRow(
            'Итого по району',
            $totals['totalMosruPerDist'] + $totals['totalPhonePerDist'] + $totals['totalLiveQueuePerDist'] + $totals['totalAppointmentPerDist'],
            $totals['totalMosruPerDist'],
            $totals['totalPhonePerDist'],
            $totals['totalLiveQueuePerDist'],
            $totals['totalAppointmentPerDist'],
            $totals['totalVisitsPerDist'],
            $totals['totalServicesPerDist'],
            self::STYLE_LIGHTGREY
        );

        /*
         * Итог по последнему округу
         */
        $this->renderTotalRow(
            'Итого по округу',
            $totals['totalMosruPerArea'] + $totals['totalPhonePerArea'] + $totals['totalLiveQueuePerArea'] + $totals['totalAppointmentPerArea'],
            $totals['totalMosruPerArea'],
            $totals['totalPhonePerArea'],
            $totals['totalLiveQueuePerArea'],
            $totals['totalAppointmentPerArea'],
            $totals['totalVisitsPerArea'],
            $totals['totalServicesPerArea'],
            self::STYLE_LIGHTGREY
        );

        /*
         * ВСЕГО
         */
        $this->renderTotalRow(
            'ВСЕГО',
            $totals['totalMosru'] + $totals['totalPhone'] + $totals['totalLiveQueue'] + $totals['totalAppointment'],
            $totals['totalMosru'],
            $totals['totalPhone'],
            $totals['totalLiveQueue'],
            $totals['totalAppointment'],
            $totals['totalVisits'],
            $totals['totalServices'],
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

        $str = '<row r="' . $this->currentRow . '" spans="1:8">'
            . '<c r="A' . $this->currentRow . '" s="' . $style . '" t="str"><v>' . $title . '</v></c>'
            . '<c r="B' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="C' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="D' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="E' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="F' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="G' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="H' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;
    }

    /**
     * @param string $title
     * @param int    $total
     * @param int    $totalMosru
     * @param int    $totalPhone
     * @param int    $totalLiveQueue
     * @param int    $totalAppointment
     * @param int    $totalVisits
     * @param int    $totalServices
     * @param int    $style
     */
    private function renderTotalRow($title, $total, $totalMosru, $totalPhone, $totalLiveQueue, $totalAppointment, $totalVisits, $totalServices, $style)
    {
        $title = $this->cleanupCellValue($title);

        $str = '<row r="' . $this->currentRow . '" spans="1:8">'
            . '<c r="A' . $this->currentRow . '" s="' . $style . '" t="str"><v>' . $title . '</v></c>'
            . '<c r="B' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $total . '</v></c>'
            . '<c r="C' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalMosru . '</v></c>'
            . '<c r="D' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalPhone . '</v></c>'
            . '<c r="E' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalLiveQueue . '</v></c>'
            . '<c r="F' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalAppointment . '</v></c>'
            . '<c r="G' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalVisits . '</v></c>'
            . '<c r="H' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalServices . '</v></c>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;
    }

    /**
     * @param array $row
     */
    private function renderRow($row)
    {
        $title = empty($row['short_name']) ? self::NO_ORGANIZATION : $this->cleanupCellValue($row['short_name']);

        $mosruVisits = ArrayHelper::getValue($row, 'mosruVisitsQuery', 0);
        $phoneVisits = ArrayHelper::getValue($row, 'phoneVisitsQuery', 0);
        $liveQueueVisits = ArrayHelper::getValue($row, 'liveQueueVisitsQuery', 0);
        $appointmentVisits = ArrayHelper::getValue($row, 'appointmentVisitsQuery', 0);
        $totalVisits = ArrayHelper::getValue($row, 'totalVisitsQuery', 0);
        $servicesCounter = ArrayHelper::getValue($row, 'servicesCounter', 0);
        $total = $mosruVisits + $phoneVisits + $liveQueueVisits + $appointmentVisits;

        $str = '<row r="' . $this->currentRow . '" spans="1:8">'
            . '<c r="A' . $this->currentRow . '" s="3" t="str"><v>' . $title . '</v></c>'
            . '<c r="B' . $this->currentRow . '"><v>' . $total . '</v></c>'
            . '<c r="C' . $this->currentRow . '"><v>' . $mosruVisits . '</v></c>'
            . '<c r="D' . $this->currentRow . '"><v>' . $phoneVisits . '</v></c>'
            . '<c r="E' . $this->currentRow . '"><v>' . $liveQueueVisits . '</v></c>'
            . '<c r="F' . $this->currentRow . '"><v>' . $appointmentVisits . '</v></c>'
            . '<c r="G' . $this->currentRow . '"><v>' . $totalVisits . '</v></c>'
            . '<c r="H' . $this->currentRow . '"><v>' . $servicesCounter . '</v></c>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;
    }
}
