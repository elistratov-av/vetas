<?php

namespace app\modules\adminv\models\excel;

use yii\helpers\ArrayHelper;

/**
 * Отчет по контролю спроса
 *
 * Class ServicesReportExport
 * @package app\modules\adminv\models\excel
 */
class VetServicesReportExport extends AbstractReportExport
{
    const STYLE_TYPEGREY = 4;
    const STYLE_SPECGREY = 6;
    const STYLE_ORGGREY = 8;
//    const STYLE_TYPEGREY = 10;
    const STYLE_TOTALGRAY = 12;

    /**
     * @var string
     */
    protected $filename = 'Отчет об оказании ветеринарных услуг c {from} по {to}';
    /**
     * @var string
     */
    protected $maxColumn = 'L';
    /**
     * @var string
     */
    protected $cols = '<cols>'
    . '<col min="1" max="1" width="70" customWidth="true" style="3"/>'
    . '<col min="2" max="2" width="15" customWidth="true" style="0"/>'
    . '<col min="3" max="3" width="15" customWidth="true" style="0"/>'
    . '<col min="4" max="4" width="15" customWidth="true" style="0"/>'
    . '<col min="5" max="5" width="15" customWidth="true" style="0"/>'
    . '<col min="6" max="6" width="15" customWidth="true" style="0"/>'
    . '<col min="7" max="7" width="15" customWidth="true" style="0"/>'
    . '<col min="8" max="8" width="15" customWidth="true" style="0"/>'
    . '<col min="9" max="9" width="15" customWidth="true" style="0"/>'
    . '<col min="10" max="10" width="15" customWidth="true" style="0"/>'
    . '<col min="11" max="11" width="15" customWidth="true" style="0"/>'
    . '<col min="12" max="12" width="15" customWidth="true" style="0"/>'
    . '</cols>';

    /**
     * @var int
     */
    private $currentSpecId = false;
    /**
     * @var int
     */
    private $currentOrgId = false;
    /**
     * @var int
     */
    private $currentTypeId = false;
    /**
     * @var array
     */
    private $totals = [
        'totalPaidPerType' => 0,
        'totalPaidPerSpec' => 0,
        'totalPaidPerOrg' => 0,
        'totalPaid' => 0,
        'totalAmountPerType' => 0,
        'totalAmountPerSpec' => 0,
        'totalAmountPerOrg' => 0,
        'totalAmount' => 0,
        'totalRabiesPerType' => 0,
        'totalRabiesPerSpec' => 0,
        'totalRabiesPerOrg' => 0,
        'totalRabies' => 0,
        'totalFreePerType' => 0,
        'totalFreePerSpec' => 0,
        'totalFreePerOrg' => 0,
        'totalFree' => 0,
        'totalBlindPerType' => 0,
        'totalBlindPerSpec' => 0,
        'totalBlindPerOrg' => 0,
        'totalBlind' => 0,
        'totalVeteranPerType' => 0,
        'totalVeteranPerSpec' => 0,
        'totalVeteranPerOrg' => 0,
        'totalVeteran' => 0,
        'totalDisabledPerType' => 0,
        'totalDisabledPerSpec' => 0,
        'totalDisabledPerOrg' => 0,
        'totalDisabled' => 0,
        'totalF1PerType' => 0,
        'totalF1PerSpec' => 0,
        'totalF1PerOrg' => 0,
        'totalF1' => 0,
        'totalF4PerType' => 0,
        'totalF4PerSpec' => 0,
        'totalF4PerOrg' => 0,
        'totalF4' => 0,
        'totalTSPerType' => 0,
        'totalTSPerSpec' => 0,
        'totalTSPerOrg' => 0,
        'totalTS' => 0,
    ];
    /**
     * @var array
     */
    private $rows = [];

    /**
     * @inheritDoc
     */
    protected function renderHead()
    {
        $this->mergeCells = '<mergeCell ref="A1:L1"/>';



        $this->mergeCells = '<mergeCell ref="G3:G4"/>';
        $this->mergeCells = '<mergeCell ref="H3:H4"/>';
        $this->mergeCells = '<mergeCell ref="I3:I4"/>';

        $this->mergeCells = '<mergeCell ref="J3:L3"/>';

        $sst = '<si><t>' . $this->title . '</t></si>';
        $this->writeLn($this->stringFile, $sst);

        $str = '<row r="' . $this->currentRow . '" spans="1:12" customHeight="1" ht="30">'
            . '<c r="A' . $this->currentRow . '" s="1" t="s"><v>0</v></c>'
            . '<c r="B' . $this->currentRow . '" s="1"/>'
            . '<c r="C' . $this->currentRow . '" s="1"/>'
            . '<c r="D' . $this->currentRow . '" s="1"/>'
            . '<c r="E' . $this->currentRow . '" s="1"/>'
            . '<c r="F' . $this->currentRow . '" s="1"/>'
            . '<c r="G' . $this->currentRow . '" s="1"/>'
            . '<c r="H' . $this->currentRow . '" s="1"/>'
            . '<c r="I' . $this->currentRow . '" s="1"/>'
            . '<c r="J' . $this->currentRow . '" s="1"/>'
            . '<c r="K' . $this->currentRow . '" s="1"/>'
            . '<c r="L' . $this->currentRow . '" s="1"/>'
            . '</row>';
//        var_dump($this->writeLn($this->sheetFile, $str)); die();
        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;

        $this->mergeCells = '<mergeCell ref="A2:A4"/>';
        $this->mergeCells = '<mergeCell ref="B2:B4"/>';
        $this->mergeCells = '<mergeCell ref="C2:C4"/>';
        $this->mergeCells = '<mergeCell ref="D2:D4"/>';
        $this->mergeCells = '<mergeCell ref="E2:E4"/>';
        $this->mergeCells = '<mergeCell ref="F2:F4"/>';
        $this->mergeCells = '<mergeCell ref="G2:L2"/>';


        $str = '<row r="' . $this->currentRow . '" spans="1:12" customHeight="1" ht="30">'
            . '<c r="A' . $this->currentRow . '" s="2" t="str"><v>Наименование ветеринарных услуг</v></c>'
            . '<c r="B' . $this->currentRow . '" s="2" t="str"><v>Тарифы с НДС</v></c>'
            . '<c r="C' . $this->currentRow . '" s="2" t="str"><v>Количество платных ветеринарных услуг</v></c>'
            . '<c r="D' . $this->currentRow . '" s="2" t="str"><v>Общая стоимость платных услуг</v></c>'
            . '<c r="E' . $this->currentRow . '" s="2" t="str"><v>Вакцина Рабикан</v></c>'
            . '<c r="F' . $this->currentRow . '" s="2" t="str"><v>Количество услуг со 100% скидкой</v></c>'
            . '<c r="G' . $this->currentRow . '" s="2" t="str"><v>Количество ветеринарных услуг, оказанных в рамках госзадания</v></c>'
            . '<c r="H' . $this->currentRow . '" s="2"/>'
            . '<c r="I' . $this->currentRow . '" s="2"/>'
            . '<c r="J' . $this->currentRow . '" s="2"/>'
            . '<c r="K' . $this->currentRow . '" s="2"/>'
            . '<c r="L' . $this->currentRow . '" s="2"/>'
            . '</row>';
        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;

        $str = '<row r="' . $this->currentRow . '" spans="1:12" customHeight="1" ht="30">'
            . '<c r="G' . $this->currentRow . '" s="2" t="str"><v>Инвалиды по зрению</v></c>'
            . '<c r="H' . $this->currentRow . '" s="2" t="str"><v>Ветераны ВОВ</v></c>'
            . '<c r="I' . $this->currentRow . '" s="2" t="str"><v>Инвалиды 1 группы</v></c>'
            . '<c r="J' . $this->currentRow . '" s="2" t="str"><v>Оформленные ВСД</v></c>'
            . '</row>';
        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;

        $str = '<row r="' . $this->currentRow . '" spans="1:12" customHeight="1" ht="30">'
            . '<c r="J' . $this->currentRow . '" s="2" t="str"><v>Форма 1</v></c>'
            . '<c r="K' . $this->currentRow . '" s="2" t="str"><v>Форма 2</v></c>'
            . '<c r="L' . $this->currentRow . '" s="2" t="str"><v>Форма TC</v></c>'
            . '</row>';
        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;


    }

    /**
     * @inheritDoc
     */
    protected function renderBody()
    {
        $query = clone $this->query;
        $query->orderBy([]);
        $total = $query->count();

        if ($total == 0) {
            $this->renderBatch(true);
        } else {
            $batchSize = 10;
            $batches = (int)ceil($total / $batchSize);
            foreach ($this->query->batch($batchSize) as $i => $rows) {
                $this->rows = [];
                foreach ($rows as $row) {
                    $this->rows[] = $row;
                }
                $this->renderBatch($i == ($batches - 1));
            }
        }
    }

    /**
     * @param bool $lastBatch
     */
    private function renderBatch($lastBatch = false)
    {
        foreach ($this->rows as $row) {
            // если данные в текущем типе услуг кончились, выводим итог
            if (($this->currentTypeId != $row['type_id']
                && $this->currentTypeId != false
                || (($this->currentSpecId != $row['id_spec']
                    || $this->currentOrgId != $row['id_organization']))
                && $this->currentTypeId != false)
            ) {
                $this->renderTotalRow(
                    'Итого по типу ',
                    $this->totals['totalPaidPerType'],
                    $this->totals['totalAmountPerType'],
                    $this->totals['totalRabiesPerType'],
                    $this->totals['totalFreePerType'],
                    $this->totals['totalBlindPerType'],
                    $this->totals['totalVeteranPerType'],
                    $this->totals['totalDisabledPerType'],
                    $this->totals['totalF1PerType'],
                    $this->totals['totalF4PerType'],
                    $this->totals['totalTSPerType'],
                    self::STYLE_TYPEGREY
                );
                $this->currentTypeId = false;
                $this->totals['totalPaidPerType'] = 0;
                $this->totals['totalAmountPerType'] = 0;
                $this->totals['totalRabiesPerType'] = 0;
                $this->totals['totalFreePerType'] = 0;
                $this->totals['totalBlindPerType'] = 0;
                $this->totals['totalVeteranPerType'] = 0;
                $this->totals['totalDisabledPerType'] = 0;
                $this->totals['totalF1PerType'] = 0;
                $this->totals['totalF4PerType'] = 0;
                $this->totals['totalTSPerType'] = 0;
            }

            // если данные у текущего спеца кончились, выводим итог
            if ($this->currentSpecId != $row['id_spec']
                && $this->currentSpecId != false
                || (($this->currentOrgId != $row['id_organization']
                    && $this->currentTypeId != false))
            ) {
                $this->renderTotalRow(
                    'Итого по специалисту ',
                    $this->totals['totalPaidPerSpec'],
                    $this->totals['totalAmountPerSpec'],
                    $this->totals['totalRabiesPerSpec'],
                    $this->totals['totalFreePerSpec'],
                    $this->totals['totalBlindPerSpec'],
                    $this->totals['totalVeteranPerSpec'],
                    $this->totals['totalDisabledPerSpec'],
                    $this->totals['totalF1PerSpec'],
                    $this->totals['totalF4PerSpec'],
                    $this->totals['totalTSPerSpec'],
                    self::STYLE_SPECGREY
                );
                $this->currentSpecId = false;
                $this->totals['totalPaidPerSpec'] = 0;
                $this->totals['totalAmountPerSpec'] = 0;
                $this->totals['totalRabiesPerSpec'] = 0;
                $this->totals['totalFreePerSpec'] = 0;
                $this->totals['totalBlindPerSpec'] = 0;
                $this->totals['totalVeteranPerSpec'] = 0;
                $this->totals['totalDisabledPerSpec'] = 0;
                $this->totals['totalF1PerSpec'] = 0;
                $this->totals['totalF4PerSpec'] = 0;
                $this->totals['totalTSPerSpec'] = 0;
            }


            // если данные в текущей организации кончились, выводим итог
            if ($this->currentOrgId != $row['id_organization']
                && $this->currentOrgId != false) {
                $this->renderTotalRow(
                    'Итого по организации ',
                    $this->totals['totalPaidPerOrg'],
                    $this->totals['totalAmountPerOrg'],
                    $this->totals['totalRabiesPerOrg'],
                    $this->totals['totalFreePerOrg'],
                    $this->totals['totalBlindPerOrg'],
                    $this->totals['totalVeteranPerOrg'],
                    $this->totals['totalDisabledPerOrg'],
                    $this->totals['totalF1PerOrg'],
                    $this->totals['totalF4PerOrg'],
                    $this->totals['totalTSPerOrg'],
                    self::STYLE_ORGGREY
                );
                $this->currentOrgId = false;
                $this->currentTypeId = false;
                $this->totals['totalPaidPerOrg'] = 0;
                $this->totals['totalAmountPerOrg'] = 0;
                $this->totals['totalRabiesPerOrg'] = 0;
                $this->totals['totalFreePerOrg'] = 0;
                $this->totals['totalBlindPerOrg'] = 0;
                $this->totals['totalVeteranPerOrg'] = 0;
                $this->totals['totalDisabledPerOrg'] = 0;
                $this->totals['totalF1PerOrg'] = 0;
                $this->totals['totalF4PerOrg'] = 0;
                $this->totals['totalTSPerOrg'] = 0;
            }

            // если началась новая орагнизация, выводим ее название
            if ($this->currentOrgId !== $row['id_organization']) {
                $this->currentOrgId = $row['id_organization'];
                $this->renderHeaderRow(
                    $row['short_name'],
                    self::STYLE_ORGGREY
                );
            }

            // если начался новый спец, выводим имя
            if ($this->currentSpecId !== $row['id_spec']) {
                $this->currentSpecId = $row['id_spec'];
                $this->renderHeaderRow(
                    $row['fullname'],
                    self::STYLE_SPECGREY
                );
            }

            // если начался новый тип услуг, выводим его название
            if ($this->currentTypeId !== $row['type_id']) {
                $this->currentTypeId = $row['type_id'];
                $this->renderHeaderRow(
                    $row['type_name'],
                    self::STYLE_TYPEGREY
                );
            }

            $this->renderRow($row);

            // ведем подсчет по типу услуг
            $this->totals['totalPaidPerType'] += $row['total_paid'];
            $this->totals['totalAmountPerType'] += $row['total_amount'];
            $this->totals['totalRabiesPerType'] += $row['total_rabies'];
            $this->totals['totalFreePerType'] += $row['total_free'];
            $this->totals['totalBlindPerType'] += $row['total_blind'];
            $this->totals['totalVeteranPerType'] += $row['total_veteran'];
            $this->totals['totalDisabledPerType'] += $row['total_disabled'];
            $this->totals['totalF1PerType'] += $row['total_f1'];
            $this->totals['totalF4PerType'] += $row['total_f4'];
            $this->totals['totalTSPerType'] += $row['total_ts'];

            // ведем подсчет по спецу
            $this->totals['totalPaidPerSpec'] += $row['total_paid'];
            $this->totals['totalAmountPerSpec'] += $row['total_amount'];
            $this->totals['totalRabiesPerSpec'] += $row['total_rabies'];
            $this->totals['totalFreePerSpec'] += $row['total_free'];
            $this->totals['totalBlindPerSpec'] += $row['total_blind'];
            $this->totals['totalVeteranPerSpec'] += $row['total_veteran'];
            $this->totals['totalDisabledPerSpec'] += $row['total_disabled'];
            $this->totals['totalF1PerSpec'] += $row['total_f1'];
            $this->totals['totalF4PerSpec'] += $row['total_f4'];
            $this->totals['totalTSPerSpec'] += $row['total_ts'];

            // ведем подсчет по организации
            $this->totals['totalPaidPerOrg'] += $row['total_paid'];
            $this->totals['totalAmountPerOrg'] += $row['total_amount'];
            $this->totals['totalRabiesPerOrg'] += $row['total_rabies'];
            $this->totals['totalFreePerOrg'] += $row['total_free'];
            $this->totals['totalBlindPerOrg'] += $row['total_blind'];
            $this->totals['totalVeteranPerOrg'] += $row['total_veteran'];
            $this->totals['totalDisabledPerOrg'] += $row['total_disabled'];
            $this->totals['totalF1PerOrg'] += $row['total_f1'];
            $this->totals['totalF4PerOrg'] += $row['total_f4'];
            $this->totals['totalTSPerOrg'] += $row['total_ts'];

            // ведем общий подсчет
            $this->totals['totalPaid'] += $row['total_paid'];
            $this->totals['totalAmount'] += $row['total_amount'];
            $this->totals['totalRabies'] += $row['total_rabies'];
            $this->totals['totalFree'] += $row['total_free'];
            $this->totals['totalBlind'] += $row['total_blind'];
            $this->totals['totalVeteran'] += $row['total_veteran'];
            $this->totals['totalDisabled'] += $row['total_disabled'];
            $this->totals['totalF1'] += $row['total_f1'];
            $this->totals['totalF4'] += $row['total_f4'];
            $this->totals['totalTS'] += $row['total_ts'];
        }

        if ($lastBatch === true && !empty($row)) {
            /*
             * Итог по последнему типу услуг
             */
            $this->renderTotalRow(
                $row['type_name'] . ': итого ',
                $this->totals['totalPaidPerType'],
                $this->totals['totalAmountPerType'],
                $this->totals['totalRabiesPerType'],
                $this->totals['totalFreePerType'],
                $this->totals['totalBlindPerType'],
                $this->totals['totalVeteranPerType'],
                $this->totals['totalDisabledPerType'],
                $this->totals['totalF1PerType'],
                $this->totals['totalF4PerType'],
                $this->totals['totalTSPerType'],
                self::STYLE_TYPEGREY
            );

            /*
             * Итог по последнему спецу
             */
            $this->renderTotalRow(
                'Итого по специалисту ',
                $this->totals['totalPaidPerSpec'],
                $this->totals['totalAmountPerSpec'],
                $this->totals['totalRabiesPerSpec'],
                $this->totals['totalFreePerSpec'],
                $this->totals['totalBlindPerSpec'],
                $this->totals['totalVeteranPerSpec'],
                $this->totals['totalDisabledPerSpec'],
                $this->totals['totalF1PerSpec'],
                $this->totals['totalF4PerSpec'],
                $this->totals['totalTSPerSpec'],
                self::STYLE_SPECGREY
            );

            /*
             * Итог по последней организации
             */
            $this->renderTotalRow(
                'Итого по организации ',
                $this->totals['totalPaidPerOrg'],
                $this->totals['totalAmountPerOrg'],
                $this->totals['totalRabiesPerOrg'],
                $this->totals['totalFreePerOrg'],
                $this->totals['totalBlindPerOrg'],
                $this->totals['totalVeteranPerOrg'],
                $this->totals['totalDisabledPerOrg'],
                $this->totals['totalF1PerOrg'],
                $this->totals['totalF4PerOrg'],
                $this->totals['totalTSPerOrg'],
                self::STYLE_ORGGREY
            );

            /*
             * ВСЕГО
             */
            $this->renderTotalRow(
                'ВСЕГО ',
                $this->totals['totalPaid'],
                $this->totals['totalAmount'],
                $this->totals['totalRabies'],
                $this->totals['totalFree'],
                $this->totals['totalBlind'],
                $this->totals['totalVeteran'],
                $this->totals['totalDisabled'],
                $this->totals['totalF1'],
                $this->totals['totalF4'],
                $this->totals['totalTS'],
                self::STYLE_TOTALGRAY
            );
        }
    }

    /**
     * @param string $title
     * @param        $style
     */
    private function renderHeaderRow($title, $style)
    {
        $title = $this->cleanupCellValue($title);

        $str = '<row r="' . $this->currentRow . '" spans="1:12">'
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
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;
    }

    /**
     * @param string $typeName
     * @param        $totalCountServicesPerType
     * @param        $totalSumAmountPerType
     * @param        $style
     */
    private function renderTotalRow($title, $totalPaid, $totalFree, $totalBlind,
                                    $totalVeteran, $totalDisabled, $totalRabies,
                                    $totalF1, $totalF4, $totalTS, $totalAmount, $style)
    {
        $title = $this->cleanupCellValue($title);
        $totalPaid = $this->cleanupCellValue($totalPaid);
        $totalFree = $this->cleanupCellValue($totalFree);
        $totalBlind = $this->cleanupCellValue($totalBlind);
        $totalVeteran = $this->cleanupCellValue($totalVeteran);
        $totalDisabled = $this->cleanupCellValue($totalDisabled);
        $totalRabies = $this->cleanupCellValue($totalRabies);
        $totalF1 = $this->cleanupCellValue($totalF1);
        $totalF4 = $this->cleanupCellValue($totalF4);
        $totalTS = $this->cleanupCellValue($totalTS);
        $totalAmount = $this->cleanupCellValue($totalAmount);

        $str = '<row r="' . $this->currentRow . '" spans="1:12">'
            . '<c r="A' . $this->currentRow . '" s="' . $style . '" t="str"><v>' . $title . '</v></c>'
            . '<c r="B' . $this->currentRow . '" s="' . ($style + 1) . '" />'
            . '<c r="C' . $this->currentRow . '" s="' . ($style + 1) . '" /><v>' . $totalPaid . '</v></c>'
            . '<c r="D' . $this->currentRow . '" s="' . ($style + 1) . '" t="str"><v>' . $totalAmount . '</v></c>'
            . '<c r="E' . $this->currentRow . '" s="' . ($style + 1) . '" t="str"><v>' . $totalRabies . '</v></c>'
            . '<c r="F' . $this->currentRow . '" s="' . ($style + 1) . '" t="str"><v>' . $totalFree . '</v></c>'
            . '<c r="G' . $this->currentRow . '" s="' . ($style + 1) . '" t="str"><v>' . $totalBlind . '</v></c>'
            . '<c r="H' . $this->currentRow . '" s="' . ($style + 1) . '" t="str"><v>' . $totalVeteran . '</v></c>'
            . '<c r="I' . $this->currentRow . '" s="' . ($style + 1) . '" t="str"><v>' . $totalDisabled . '</v></c>'
            . '<c r="J' . $this->currentRow . '" s="' . ($style + 1) . '" t="str"><v>' . $totalF1 . '</v></c>'
            . '<c r="K' . $this->currentRow . '" s="' . ($style + 1) . '" t="str"><v>' . $totalF4 . '</v></c>'
            . '<c r="L' . $this->currentRow . '" s="' . ($style + 1) . '" t="str"><v>' . $totalTS . '</v></c>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;
    }

    /**
     * @param array $row
     */
    private function renderRow($row)
    {
        $title = $this->cleanupCellValue(ArrayHelper::getValue($row, 'name', ''));
        $price = $this->cleanupCellValue(ArrayHelper::getValue($row, 'price', ''));
        $total_paid = $this->cleanupCellValue(ArrayHelper::getValue($row, 'total_paid', ''));
        $total_amount = $this->cleanupCellValue(ArrayHelper::getValue($row, 'total_amount', ''));
        $total_rabies = $this->cleanupCellValue(ArrayHelper::getValue($row, 'total_rabies', ''));
        $total_free = $this->cleanupCellValue(ArrayHelper::getValue($row, 'total_free', ''));
        $total_blind = $this->cleanupCellValue(ArrayHelper::getValue($row, 'total_blind', ''));
        $total_veteran = $this->cleanupCellValue(ArrayHelper::getValue($row, 'total_veteran', ''));
        $total_disabled = $this->cleanupCellValue(ArrayHelper::getValue($row, 'total_disabled', ''));
        $total_f1 = $this->cleanupCellValue(ArrayHelper::getValue($row, 'total_f1', ''));
        $total_f4 = $this->cleanupCellValue(ArrayHelper::getValue($row, 'total_f4', ''));
        $total_ts = $this->cleanupCellValue(ArrayHelper::getValue($row, 'total_ts', ''));

        $str = '<row r="' . $this->currentRow . '" spans="1:12" customHeight="1" ht="30" outlineLevel="1">'
            . '<c r="A' . $this->currentRow . '" s="3" t="str"><v>' . $title . '</v></c>'
            . '<c r="B' . $this->currentRow . '"><v>' . $price . '</v></c>'
            . '<c r="C' . $this->currentRow . '"><v>' . $total_paid . '</v></c>'
            . '<c r="D' . $this->currentRow . '"><v>' . $total_amount . '</v></c>'
            . '<c r="E' . $this->currentRow . '"><v>' . $total_rabies . '</v></c>'
            . '<c r="F' . $this->currentRow . '"><v>' . $total_free . '</v></c>'
            . '<c r="G' . $this->currentRow . '"><v>' . $total_blind . '</v></c>'
            . '<c r="H' . $this->currentRow . '"><v>' . $total_veteran . '</v></c>'
            . '<c r="I' . $this->currentRow . '"><v>' . $total_disabled . '</v></c>'
            . '<c r="J' . $this->currentRow . '"><v>' . $total_f1 . '</v></c>'
            . '<c r="K' . $this->currentRow . '"><v>' . $total_f4 . '</v></c>'
            . '<c r="L' . $this->currentRow . '"><v>' . $total_ts . '</v></c>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;
    }
}
