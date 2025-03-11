<?php

namespace app\modules\adminv\models\excel;

use yii\helpers\ArrayHelper;

/**
 * Общий отчет по приемам
 *
 * Class FullVisitsReportExport
 * @package app\modules\adminv\models\excel
 */
class FullVisitsReportExport extends AbstractReportExport
{
    const STYLE_LIGHTGREY = 6;
    const STYLE_MIDGREY = 4;
    const STYLE_DARKGRAY = 8;

    /**
     * @var string
     */
    protected $filename = 'Общий отчет по приемам c {from} по {to}';
    /**
     * @var string
     */
    protected $title = 'Общий отчет по приемам за период c {from} по {to}';
    /**
     * @var string
     */
    protected $maxColumn = 'P';
    /**
     * @var string
     */
    protected $cols = '<cols>'
    . '<col min="1" max="1" width="30" customWidth="true" style="1"/>'
    . '<col min="2" max="2" width="9" customWidth="true" style="0"/>'
    . '<col min="6" max="6" width="9" customWidth="true" style="0"/>'
    . '<col min="10" max="10" width="9" customWidth="true" style="0"/>'
    . '<col min="14" max="14" width="9" customWidth="true" style="0"/>'
    . '<col min="3" max="3" width="10" customWidth="true" style="0"/>'
    . '<col min="4" max="4" width="12" customWidth="true" style="0"/>'
    . '<col min="5" max="5" width="12" customWidth="true" style="0"/>'
    . '<col min="7" max="7" width="10" customWidth="true" style="0"/>'
    . '<col min="8" max="8" width="12" customWidth="true" style="0"/>'
    . '<col min="9" max="9" width="12" customWidth="true" style="0"/>'
    . '<col min="11" max="11" width="10" customWidth="true" style="0"/>'
    . '<col min="12" max="12" width="12" customWidth="true" style="0"/>'
    . '<col min="13" max="13" width="12" customWidth="true" style="0"/>'
    . '<col min="15" max="15" width="10" customWidth="true" style="0"/>'
    . '<col min="16" max="16" width="12" customWidth="true" style="0"/>'
    . '</cols>';

    /**
     * @inheritDoc
     */
    protected function renderHead()
    {
        $this->mergeCells = '<mergeCell ref="A1:P1"/>'
            . '<mergeCell ref="A2:P2"/>'
            . '<mergeCell ref="B3:E3"/>'
            . '<mergeCell ref="F3:I3"/>'
            . '<mergeCell ref="J3:M3"/>'
            . '<mergeCell ref="N3:P3"/>';

        $sst = '<si><t>' . $this->title . '</t></si>';
        $this->writeLn($this->stringFile, $sst);

        $str = '<row r="' . $this->currentRow . '" spans="1:16" customHeight="1" ht="30">'
            . '<c r="A' . $this->currentRow . '" s="2" t="s"><v>0</v></c>'
            . '<c r="B' . $this->currentRow . '" s="2"/>'
            . '<c r="C' . $this->currentRow . '" s="2"/>'
            . '<c r="D' . $this->currentRow . '" s="2"/>'
            . '<c r="E' . $this->currentRow . '" s="2"/>'
            . '<c r="F' . $this->currentRow . '" s="2"/>'
            . '<c r="G' . $this->currentRow . '" s="2"/>'
            . '<c r="H' . $this->currentRow . '" s="2"/>'
            . '<c r="I' . $this->currentRow . '" s="2"/>'
            . '<c r="J' . $this->currentRow . '" s="2"/>'
            . '<c r="K' . $this->currentRow . '" s="2"/>'
            . '<c r="L' . $this->currentRow . '" s="2"/>'
            . '<c r="M' . $this->currentRow . '" s="2"/>'
            . '<c r="N' . $this->currentRow . '" s="2"/>'
            . '<c r="O' . $this->currentRow . '" s="2"/>'
            . '<c r="P' . $this->currentRow . '" s="2"/>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;

        $str = '<row r="' . $this->currentRow . '" spans="1:16">'
            . '<c r="A' . $this->currentRow . '" s="3" t="str"><v>Обработано обращений (записей на прием)</v></c>'
            . '<c r="B' . $this->currentRow . '" s="3"/>'
            . '<c r="C' . $this->currentRow . '" s="3"/>'
            . '<c r="D' . $this->currentRow . '" s="3"/>'
            . '<c r="E' . $this->currentRow . '" s="3"/>'
            . '<c r="F' . $this->currentRow . '" s="3"/>'
            . '<c r="G' . $this->currentRow . '" s="3"/>'
            . '<c r="H' . $this->currentRow . '" s="3"/>'
            . '<c r="I' . $this->currentRow . '" s="3"/>'
            . '<c r="J' . $this->currentRow . '" s="3"/>'
            . '<c r="K' . $this->currentRow . '" s="3"/>'
            . '<c r="L' . $this->currentRow . '" s="3"/>'
            . '<c r="M' . $this->currentRow . '" s="3"/>'
            . '<c r="N' . $this->currentRow . '" s="3"/>'
            . '<c r="O' . $this->currentRow . '" s="3"/>'
            . '<c r="P' . $this->currentRow . '" s="3"/>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;

        $str = '<row r="' . $this->currentRow . '" spans="1:16">'
            . '<c r="A' . $this->currentRow . '" s="3"/>'
            . '<c r="B' . $this->currentRow . '" s="3" t="str"><v>mos.ru</v></c>'
            . '<c r="C' . $this->currentRow . '" s="3"/>'
            . '<c r="D' . $this->currentRow . '" s="3"/>'
            . '<c r="E' . $this->currentRow . '" s="3"/>'
            . '<c r="F' . $this->currentRow . '" s="3" t="str"><v>Телефон</v></c>'
            . '<c r="G' . $this->currentRow . '" s="3"/>'
            . '<c r="H' . $this->currentRow . '" s="3"/>'
            . '<c r="I' . $this->currentRow . '" s="3"/>'
            . '<c r="J' . $this->currentRow . '" s="3" t="str"><v>Живая очередь</v></c>'
            . '<c r="K' . $this->currentRow . '" s="3"/>'
            . '<c r="L' . $this->currentRow . '" s="3"/>'
            . '<c r="M' . $this->currentRow . '" s="3"/>'
            . '<c r="N' . $this->currentRow . '" s="3" t="str"><v>Направление</v></c>'
            . '<c r="O' . $this->currentRow . '" s="3"/>'
            . '<c r="P' . $this->currentRow . '" s="3"/>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;

        $str = '<row r="' . $this->currentRow . '" spans="1:16">'
            . '<c r="A' . $this->currentRow . '" s="3"/>'
            . '<c r="B' . $this->currentRow . '" s="3" t="str"><v>Создано</v></c>'
            . '<c r="C' . $this->currentRow . '" s="3" t="str"><v>Отменено</v></c>'
            . '<c r="D' . $this->currentRow . '" s="3" t="str"><v>Перенесено</v></c>'
            . '<c r="E' . $this->currentRow . '" s="3" t="str"><v>Завершено</v></c>'
            . '<c r="F' . $this->currentRow . '" s="3" t="str"><v>Создано</v></c>'
            . '<c r="G' . $this->currentRow . '" s="3" t="str"><v>Отменено</v></c>'
            . '<c r="H' . $this->currentRow . '" s="3" t="str"><v>Перенесено</v></c>'
            . '<c r="I' . $this->currentRow . '" s="3" t="str"><v>Завершено</v></c>'
            . '<c r="J' . $this->currentRow . '" s="3" t="str"><v>Создано</v></c>'
            . '<c r="K' . $this->currentRow . '" s="3" t="str"><v>Отменено</v></c>'
            . '<c r="L' . $this->currentRow . '" s="3" t="str"><v>Перенесено</v></c>'
            . '<c r="M' . $this->currentRow . '" s="3" t="str"><v>Завершено</v></c>'
            . '<c r="N' . $this->currentRow . '" s="3" t="str"><v>Создано</v></c>'
            . '<c r="O' . $this->currentRow . '" s="3" t="str"><v>Отменено</v></c>'
            . '<c r="P' . $this->currentRow . '" s="3" t="str"><v>Завершено</v></c>'
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
            'totalMosruCreatedPerDist' => 0,
            'totalMosruCancelledPerDist' => 0,
            'totalMosruTransferedPerDist' => 0,
            'totalMosruFinishedPerDist' => 0,
            'totalMosruCreatedPerArea' => 0,
            'totalMosruCancelledPerArea' => 0,
            'totalMosruTransferedPerArea' => 0,
            'totalMosruFinishedPerArea' => 0,
            'totalMosruCreated' => 0,
            'totalMosruCancelled' => 0,
            'totalMosruTransfered' => 0,
            'totalMosruFinished' => 0,

            'totalPhoneCreatedPerDist' => 0,
            'totalPhoneCancelledPerDist' => 0,
            'totalPhoneTransferedPerDist' => 0,
            'totalPhoneFinishedPerDist' => 0,
            'totalPhoneCreatedPerArea' => 0,
            'totalPhoneCancelledPerArea' => 0,
            'totalPhoneTransferedPerArea' => 0,
            'totalPhoneFinishedPerArea' => 0,
            'totalPhoneCreated' => 0,
            'totalPhoneCancelled' => 0,
            'totalPhoneTransfered' => 0,
            'totalPhoneFinished' => 0,

            'totalLqCreatedPerDist' => 0,
            'totalLqCancelledPerDist' => 0,
            'totalLqTransferedPerDist' => 0,
            'totalLqFinishedPerDist' => 0,
            'totalLqCreatedPerArea' => 0,
            'totalLqCancelledPerArea' => 0,
            'totalLqTransferedPerArea' => 0,
            'totalLqFinishedPerArea' => 0,
            'totalLqCreated' => 0,
            'totalLqCancelled' => 0,
            'totalLqTransfered' => 0,
            'totalLqFinished' => 0,

            'totalWdCreatedPerDist' => 0,
            'totalWdCancelledPerDist' => 0,
            'totalWdFinishedPerDist' => 0,
            'totalWdCreatedPerArea' => 0,
            'totalWdCancelledPerArea' => 0,
            'totalWdFinishedPerArea' => 0,
            'totalWdCreated' => 0,
            'totalWdCancelled' => 0,
            'totalWdFinished' => 0
        ];

        foreach ($this->data as $row) {
            // если данные в текущем районе кончились, выводим итог
            if($currentDistId !== $row['id_district'] && $currentDistId !== false) {
                $this->renderTotalRow(
                    'Итого по району',
                    $totals['totalMosruCreatedPerDist'],
                    $totals['totalMosruCancelledPerDist'],
                    $totals['totalMosruTransferedPerDist'],
                    $totals['totalMosruFinishedPerDist'],

                    $totals['totalPhoneCreatedPerDist'],
                    $totals['totalPhoneCancelledPerDist'],
                    $totals['totalPhoneTransferedPerDist'],
                    $totals['totalPhoneFinishedPerDist'],

                    $totals['totalLqCreatedPerDist'],
                    $totals['totalLqCancelledPerDist'],
                    $totals['totalLqTransferedPerDist'],
                    $totals['totalLqFinishedPerDist'],

                    $totals['totalWdCreatedPerDist'],
                    $totals['totalWdCancelledPerDist'],
                    $totals['totalWdFinishedPerDist'],

                    self::STYLE_LIGHTGREY
                );
                $currentDistId = false;
                $totals['totalMosruCreatedPerDist'] = 0;
                $totals['totalMosruCancelledPerDist'] = 0;
                $totals['totalMosruTransferedPerDist'] = 0;
                $totals['totalMosruFinishedPerDist'] = 0;

                $totals['totalPhoneCreatedPerDist'] = 0;
                $totals['totalPhoneCancelledPerDist'] = 0;
                $totals['totalPhoneTransferedPerDist'] = 0;
                $totals['totalPhoneFinishedPerDist'] = 0;

                $totals['totalLqCreatedPerDist'] = 0;
                $totals['totalLqCancelledPerDist'] = 0;
                $totals['totalLqTransferedPerDist'] = 0;
                $totals['totalLqFinishedPerDist'] = 0;

                $totals['totalWdCreatedPerDist'] = 0;
                $totals['totalWdCancelledPerDist'] = 0;
                $totals['totalWdFinishedPerDist'] = 0;
            }

            // если данные в текущем округе кончились, выводим итог
            if($currentAreaId !== $row['id_area'] && $currentAreaId !== false) {
                $this->renderTotalRow(
                    'Итого по округу',
                    $totals['totalMosruCreatedPerArea'],
                    $totals['totalMosruCancelledPerArea'],
                    $totals['totalMosruTransferedPerArea'],
                    $totals['totalMosruFinishedPerArea'],

                    $totals['totalPhoneCreatedPerArea'],
                    $totals['totalPhoneCancelledPerArea'],
                    $totals['totalPhoneTransferedPerArea'],
                    $totals['totalPhoneFinishedPerArea'],

                    $totals['totalLqCreatedPerArea'],
                    $totals['totalLqCancelledPerArea'],
                    $totals['totalLqTransferedPerArea'],
                    $totals['totalLqFinishedPerArea'],

                    $totals['totalWdCreatedPerArea'],
                    $totals['totalWdCancelledPerArea'],
                    $totals['totalWdFinishedPerArea'],

                    self::STYLE_LIGHTGREY
                );
                $currentDistId = false;
                $totals['totalMosruCreatedPerArea'] = 0;
                $totals['totalMosruCancelledPerArea'] = 0;
                $totals['totalMosruTransferedPerArea'] = 0;
                $totals['totalMosruFinishedPerArea'] = 0;

                $totals['totalPhoneCreatedPerArea'] = 0;
                $totals['totalPhoneCancelledPerArea'] = 0;
                $totals['totalPhoneTransferedPerArea'] = 0;
                $totals['totalPhoneFinishedPerArea'] = 0;

                $totals['totalLqCreatedPerArea'] = 0;
                $totals['totalLqCancelledPerArea'] = 0;
                $totals['totalLqTransferedPerArea'] = 0;
                $totals['totalLqFinishedPerArea'] = 0;

                $totals['totalWdCreatedPerArea'] = 0;
                $totals['totalWdCancelledPerArea'] = 0;
                $totals['totalWdFinishedPerArea'] = 0;
            }

            // если начался новый округ, выводим его название
            if($currentAreaId !== $row['id_area']) {
                $currentAreaId = $row['id_area'];
                $this->renderHeaderRow(
                    $row['area_name'] ?? self::NO_AREA,
                    self::STYLE_MIDGREY
                );
            }

            // если начался новый район, выводим его название
            if($currentDistId !== $row['id_district']) {
                $currentDistId = $row['id_district'];
                $this->renderHeaderRow(
                    $row['dist_name'] ?? self::NO_DISTRICT,
                    self::STYLE_LIGHTGREY
                );
            }

            $this->renderRow($row);

            // ведем подсчет по району
            $totals['totalMosruCreatedPerDist'] += $row['mos_ru_created'];
            $totals['totalMosruCancelledPerDist'] += $row['mos_ru_cancelled'];
            $totals['totalMosruTransferedPerDist'] += $row['mos_ru_transfered'];
            $totals['totalMosruFinishedPerDist'] += $row['mos_ru_finished'];

            $totals['totalPhoneCreatedPerDist'] += $row['phone_created'];
            $totals['totalPhoneCancelledPerDist'] += $row['phone_cancelled'];
            $totals['totalPhoneTransferedPerDist'] += $row['phone_transfered'];
            $totals['totalPhoneFinishedPerDist'] += $row['phone_finished'];

            $totals['totalLqCreatedPerDist'] += $row['lq_created'];
            $totals['totalLqCancelledPerDist'] += $row['lq_cancelled'];
            $totals['totalLqTransferedPerDist'] += $row['lq_transfered'];
            $totals['totalLqFinishedPerDist'] += $row['lq_finished'];

            $totals['totalWdCreatedPerDist'] += $row['wd_created'];
            $totals['totalWdCancelledPerDist'] += $row['wd_cancelled'];
            $totals['totalWdFinishedPerDist'] += $row['wd_finished'];

            // ведем подсчет по округу
            $totals['totalMosruCreatedPerArea'] += $row['mos_ru_created'];
            $totals['totalMosruCancelledPerArea'] += $row['mos_ru_cancelled'];
            $totals['totalMosruTransferedPerArea'] += $row['mos_ru_transfered'];
            $totals['totalMosruFinishedPerArea'] += $row['mos_ru_finished'];

            $totals['totalPhoneCreatedPerArea'] += $row['phone_created'];
            $totals['totalPhoneCancelledPerArea'] += $row['phone_cancelled'];
            $totals['totalPhoneTransferedPerArea'] += $row['phone_transfered'];
            $totals['totalPhoneFinishedPerArea'] += $row['phone_finished'];

            $totals['totalLqCreatedPerArea'] += $row['lq_created'];
            $totals['totalLqCancelledPerArea'] += $row['lq_cancelled'];
            $totals['totalLqTransferedPerArea'] += $row['lq_transfered'];
            $totals['totalLqFinishedPerArea'] += $row['lq_finished'];

            $totals['totalWdCreatedPerArea'] += $row['wd_created'];
            $totals['totalWdCancelledPerArea'] += $row['wd_cancelled'];
            $totals['totalWdFinishedPerArea'] += $row['wd_finished'];

            // ведем общий подсчет
            $totals['totalMosruCreated'] += $row['mos_ru_created'];
            $totals['totalMosruCancelled'] += $row['mos_ru_cancelled'];
            $totals['totalMosruTransfered'] += $row['mos_ru_transfered'];
            $totals['totalMosruFinished'] += $row['mos_ru_finished'];

            $totals['totalPhoneCreated'] += $row['phone_created'];
            $totals['totalPhoneCancelled'] += $row['phone_cancelled'];
            $totals['totalPhoneTransfered'] += $row['phone_transfered'];
            $totals['totalPhoneFinished'] += $row['phone_finished'];

            $totals['totalLqCreated'] += $row['lq_created'];
            $totals['totalLqCancelled'] += $row['lq_cancelled'];
            $totals['totalLqTransfered'] += $row['lq_transfered'];
            $totals['totalLqFinished'] += $row['lq_finished'];

            $totals['totalWdCreated'] += $row['wd_created'];
            $totals['totalWdCancelled'] += $row['wd_cancelled'];
            $totals['totalWdFinished'] += $row['wd_finished'];
        }

        /*
         * Итог по последнему району
         */
        $this->renderTotalRow(
            'Итого по району',
            $totals['totalMosruCreatedPerDist'],
            $totals['totalMosruCancelledPerDist'],
            $totals['totalMosruTransferedPerDist'],
            $totals['totalMosruFinishedPerDist'],

            $totals['totalPhoneCreatedPerDist'],
            $totals['totalPhoneCancelledPerDist'],
            $totals['totalPhoneTransferedPerDist'],
            $totals['totalPhoneFinishedPerDist'],

            $totals['totalLqCreatedPerDist'],
            $totals['totalLqCancelledPerDist'],
            $totals['totalLqTransferedPerDist'],
            $totals['totalLqFinishedPerDist'],

            $totals['totalWdCreatedPerDist'],
            $totals['totalWdCancelledPerDist'],
            $totals['totalWdFinishedPerDist'],
            self::STYLE_LIGHTGREY
        );

        /*
         * Итог по последнему округу
         */
        $this->renderTotalRow(
            'Итого по округу',
            $totals['totalMosruCreatedPerArea'],
            $totals['totalMosruCancelledPerArea'],
            $totals['totalMosruTransferedPerArea'],
            $totals['totalMosruFinishedPerArea'],

            $totals['totalPhoneCreatedPerArea'],
            $totals['totalPhoneCancelledPerArea'],
            $totals['totalPhoneTransferedPerArea'],
            $totals['totalPhoneFinishedPerArea'],

            $totals['totalLqCreatedPerArea'],
            $totals['totalLqCancelledPerArea'],
            $totals['totalLqTransferedPerArea'],
            $totals['totalLqFinishedPerArea'],

            $totals['totalWdCreatedPerArea'],
            $totals['totalWdCancelledPerArea'],
            $totals['totalWdFinishedPerArea'],
            self::STYLE_LIGHTGREY
        );

        /*
         * ВСЕГО
         */
        $this->renderTotalRow(
            'ВСЕГО',
            $totals['totalMosruCreated'],
            $totals['totalMosruCancelled'],
            $totals['totalMosruTransfered'],
            $totals['totalMosruFinished'],

            $totals['totalPhoneCreated'],
            $totals['totalPhoneCancelled'],
            $totals['totalPhoneTransfered'],
            $totals['totalPhoneFinished'],

            $totals['totalLqCreated'],
            $totals['totalLqCancelled'],
            $totals['totalLqTransfered'],
            $totals['totalLqFinished'],

            $totals['totalWdCreated'],
            $totals['totalWdCancelled'],
            $totals['totalWdFinished'],
            self::STYLE_DARKGRAY
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

        $str = '<row r="' . $this->currentRow . '" spans="1:16">'
            . '<c r="A' . $this->currentRow . '" s="' . $style . '" t="str"><v>' . $title . '</v></c>'
            . '<c r="B' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="C' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="D' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="E' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="F' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="G' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="H' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="I' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="J' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="K' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="L' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="M' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="N' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="O' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="P' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;
    }

    /**
     * @param string $title
     * @param int    $totalMosruCreated
     * @param int    $totalMosruCancelled
     * @param int    $totalMosruTransfered
     * @param int    $totalMosruFinished
     * @param int    $totalPhoneCreated
     * @param int    $totalPhoneCancelled
     * @param int    $totalPhoneTransfered
     * @param int    $totalPhoneFinished
     * @param int    $totalLqCreated
     * @param int    $totalLqCancelled
     * @param int    $totalLqTransfered
     * @param int    $totalLqFinished
     * @param int    $totalWdCreated
     * @param int    $totalWdCancelled
     * @param int    $totalWdFinished
     * @param int    $style
     */
    private function renderTotalRow(
        $title,
        $totalMosruCreated,
        $totalMosruCancelled,
        $totalMosruTransfered,
        $totalMosruFinished,
        $totalPhoneCreated,
        $totalPhoneCancelled,
        $totalPhoneTransfered,
        $totalPhoneFinished,
        $totalLqCreated,
        $totalLqCancelled,
        $totalLqTransfered,
        $totalLqFinished,
        $totalWdCreated,
        $totalWdCancelled,
        $totalWdFinished,
        $style
    )
    {
        $title = $this->cleanupCellValue($title);

        $str = '<row r="' . $this->currentRow . '" spans="1:16">'
            . '<c r="A' . $this->currentRow . '" s="' . $style . '" t="str"><v>' . $title . '</v></c>'
            . '<c r="B' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalMosruCreated . '</v></c>'
            . '<c r="C' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalMosruCancelled . '</v></c>'
            . '<c r="D' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalMosruTransfered . '</v></c>'
            . '<c r="E' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalMosruFinished . '</v></c>'
            . '<c r="F' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalPhoneCreated . '</v></c>'
            . '<c r="G' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalPhoneCancelled . '</v></c>'
            . '<c r="H' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalPhoneTransfered . '</v></c>'
            . '<c r="I' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalPhoneFinished . '</v></c>'
            . '<c r="J' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalLqCreated . '</v></c>'
            . '<c r="K' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalLqCancelled . '</v></c>'
            . '<c r="L' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalLqTransfered . '</v></c>'
            . '<c r="M' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalLqFinished . '</v></c>'
            . '<c r="N' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalWdCreated . '</v></c>'
            . '<c r="O' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalWdCancelled . '</v></c>'
            . '<c r="P' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalWdFinished . '</v></c>'
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

        $totalMosruCreated = ArrayHelper::getValue($row, 'mos_ru_created', 0);
        $totalMosruCancelled = ArrayHelper::getValue($row, 'mos_ru_cancelled', 0);
        $totalMosruTransfered = ArrayHelper::getValue($row, 'mos_ru_transfered', 0);
        $totalMosruFinished = ArrayHelper::getValue($row, 'mos_ru_finished', 0);
        $totalPhoneCreated = ArrayHelper::getValue($row, 'phone_created', 0);
        $totalPhoneCancelled = ArrayHelper::getValue($row, 'phone_cancelled', 0);
        $totalPhoneTransfered = ArrayHelper::getValue($row, 'phone_transfered', 0);
        $totalPhoneFinished = ArrayHelper::getValue($row, 'phone_finished', 0);
        $totalLqCreated = ArrayHelper::getValue($row, 'lq_created', 0);
        $totalLqCancelled = ArrayHelper::getValue($row, 'lq_cancelled', 0);
        $totalLqTransfered = ArrayHelper::getValue($row, 'lq_transfered', 0);
        $totalLqFinished = ArrayHelper::getValue($row, 'lq_finished', 0);
        $totalWdCreated = ArrayHelper::getValue($row, 'wd_created', 0);
        $totalWdCancelled = ArrayHelper::getValue($row, 'wd_cancelled', 0);
        $totalWdFinished = ArrayHelper::getValue($row, 'wd_finished', 0);

        $str = '<row r="' . $this->currentRow . '" spans="1:16" customHeight="1" ht="15">'
            . '<c r="A' . $this->currentRow . '" s="1" t="str"><v>' . $title . '</v></c>'
            . '<c r="B' . $this->currentRow . '"><v>' . $totalMosruCreated . '</v></c>'
            . '<c r="C' . $this->currentRow . '"><v>' . $totalMosruCancelled . '</v></c>'
            . '<c r="D' . $this->currentRow . '"><v>' . $totalMosruTransfered . '</v></c>'
            . '<c r="E' . $this->currentRow . '"><v>' . $totalMosruFinished . '</v></c>'
            . '<c r="F' . $this->currentRow . '"><v>' . $totalPhoneCreated . '</v></c>'
            . '<c r="G' . $this->currentRow . '"><v>' . $totalPhoneCancelled . '</v></c>'
            . '<c r="H' . $this->currentRow . '"><v>' . $totalPhoneTransfered . '</v></c>'
            . '<c r="I' . $this->currentRow . '"><v>' . $totalPhoneFinished . '</v></c>'
            . '<c r="J' . $this->currentRow . '"><v>' . $totalLqCreated . '</v></c>'
            . '<c r="K' . $this->currentRow . '"><v>' . $totalLqCancelled . '</v></c>'
            . '<c r="L' . $this->currentRow . '"><v>' . $totalLqTransfered . '</v></c>'
            . '<c r="M' . $this->currentRow . '"><v>' . $totalLqFinished . '</v></c>'
            . '<c r="N' . $this->currentRow . '"><v>' . $totalWdCreated . '</v></c>'
            . '<c r="O' . $this->currentRow . '"><v>' . $totalWdCancelled . '</v></c>'
            . '<c r="P' . $this->currentRow . '"><v>' . $totalWdFinished . '</v></c>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;
    }
}
