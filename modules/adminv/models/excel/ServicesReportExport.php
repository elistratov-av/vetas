<?php

namespace app\modules\adminv\models\excel;

use yii\helpers\ArrayHelper;

/**
 * Отчет по контролю спроса
 *
 * Class ServicesReportExport
 * @package app\modules\adminv\models\excel
 */
class ServicesReportExport extends AbstractReportExport
{
    const STYLE_AREAGREY = 4;
    const STYLE_DISTGREY = 6;
    const STYLE_ORGGREY = 8;
    const STYLE_TYPEGREY = 10;
    const STYLE_TOTALGRAY = 12;

    /**
     * @var string
     */
    protected $filename = 'Отчет по контролю спроса c {from} по {to}';
    /**
     * @var string
     */
    protected $maxColumn = 'D';
    /**
     * @var string
     */
    protected $cols = '<cols>'
    . '<col min="1" max="1" width="70" customWidth="true" style="3"/>'
    . '<col min="2" max="2" width="15" customWidth="true" style="0"/>'
    . '<col min="3" max="3" width="15" customWidth="true" style="0"/>'
    . '<col min="4" max="4" width="15" customWidth="true" style="0"/>'
    . '</cols>';

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
    private $currentTypeId = false;
    /**
     * @var array
     */
    private $totals = [
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
        'serviceNamesPerDist' => [],
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
        $this->mergeCells = '<mergeCell ref="A1:D1"/>';

        $sst = '<si><t>' . $this->title . '</t></si>';
        $this->writeLn($this->stringFile, $sst);

        $str = '<row r="' . $this->currentRow . '" spans="1:4" customHeight="1" ht="30">'
            . '<c r="A' . $this->currentRow . '" s="1" t="s"><v>0</v></c>'
            . '<c r="B' . $this->currentRow . '" s="1"/>'
            . '<c r="C' . $this->currentRow . '" s="1"/>'
            . '<c r="D' . $this->currentRow . '" s="1"/>'
            . '</row>';
        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;

        $str = '<row r="' . $this->currentRow . '" spans="1:4" customHeight="1" ht="30">'
            . '<c r="A' . $this->currentRow . '" s="2" t="str"><v>Услуга</v></c>'
            . '<c r="B' . $this->currentRow . '" s="2" t="str"><v>Оказано количество</v></c>'
            . '<c r="C' . $this->currentRow . '" s="2" t="str"><v>Стоимость за единицу</v></c>'
            . '<c r="D' . $this->currentRow . '" s="2" t="str"><v>Стоимость услуг (общая)</v></c>'
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
            if ($this->currentTypeId !== $row['type_id']
                && $this->currentTypeId !== false
                || ($this->currentOrgId !== $row['id_organization']
                    || $this->currentDistId !== $row['id_district']
                    || $this->currentAreaId !== $row['id_area'])
                && $this->currentTypeId !== false
            ) {
                $this->renderTotalRow(
                    'Итого по типу ' . $this->totals['totalPerType'],
                    $this->totals['totalCountServicesPerType'],
                    $this->totals['totalSumAmountPerType'],
                    self::STYLE_TYPEGREY
                );
                $this->currentTypeId = false;
                $this->totals['totalPerType'] = 0;
                $this->totals['totalCountServicesPerType'] = 0;
                $this->totals['totalSumAmountPerType'] = 0;
            }

            // если данные в текущей организации кончились, выводим итог
            if ($this->currentOrgId !== $row['id_organization']
                && $this->currentOrgId !== false
                || (($this->currentDistId !== $row['id_district']
                    || $this->currentAreaId !== $row['id_area']))
                && $this->currentOrgId !== false) {
                $this->renderTotalRow(
                    'Итого по организации ' . $this->totals['totalPerOrg'],
                    $this->totals['totalCountServicesPerOrg'],
                    $this->totals['totalSumAmountPerOrg'],
                    self::STYLE_ORGGREY
                );
                $this->currentOrgId = false;
                $this->currentTypeId = false;
                $this->totals['totalPerOrg'] = 0;
                $this->totals['totalCountServicesPerOrg'] = 0;
                $this->totals['totalSumAmountPerOrg'] = 0;
            }

            // если данные в текущем районе кончились, выводим итог
            if ($this->currentDistId !== $row['id_district'] && $this->currentDistId !== false) {
                $this->renderTotalRow(
                    'Итого по району ' . count(array_unique($this->totals['serviceNamesPerDist'])),
                    $this->totals['totalCountServicesPerDist'],
                    $this->totals['totalSumAmountPerDist'],
                    self::STYLE_DISTGREY
                );
                $this->currentTypeId = false;
                $this->currentOrgId = false;
                $this->totals['serviceNamesPerDist'] = [];
                $this->totals['totalCountServicesPerDist'] = 0;
                $this->totals['totalSumAmountPerDist'] = 0;
            }

            // если данные в текущем округе кончились, выводим итог
            if ($this->currentAreaId !== $row['id_area'] && $this->currentAreaId !== false) {
                $this->renderTotalRow(
                    'Итого по округу ' . count(array_unique($this->totals['serviceNamesPerArea'])),
                    $this->totals['totalCountServicesPerArea'],
                    $this->totals['totalSumAmountPerArea'],
                    self::STYLE_AREAGREY
                );
                $this->currentTypeId = false;
                $this->currentOrgId = false;
                $this->totals['serviceNamesPerArea'] = [];
                $this->totals['totalCountServicesPerArea'] = 0;
                $this->totals['totalSumAmountPerArea'] = 0;
            }

            // если начался новый округ, выводим его название
            if ($this->currentAreaId !== $row['id_area']) {
                $this->currentAreaId = $row['id_area'];
                $this->renderHeaderRow(
                    $row['area'] ?? self::NO_AREA,
                    self::STYLE_AREAGREY
                );
            }

            // если начался новый район, выводим его название
            if ($this->currentDistId !== $row['id_district']) {
                $this->currentDistId = $row['id_district'];
                $this->renderHeaderRow(
                    $row['dist'] ?? self::NO_DISTRICT,
                    self::STYLE_DISTGREY
                );
            }

            // если началась новая орагнизация, выводим ее название
            if ($this->currentOrgId !== $row['id_organization']) {
                $this->currentOrgId = $row['id_organization'];
                $this->renderHeaderRow(
                    $row['short_name'],
                    self::STYLE_ORGGREY
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
            $this->totals['totalPerType']++;
            $this->totals['totalCountServicesPerType'] += $row['sum'];
            $this->totals['totalSumAmountPerType'] += $row['total_amount'];

            // ведем подсчет по организации
            $this->totals['totalPerOrg']++;
            $this->totals['totalCountServicesPerOrg'] += $row['sum'];
            $this->totals['totalSumAmountPerOrg'] += $row['total_amount'];

            // ведем подсчет по району
            $this->totals['serviceNamesPerDist'][] = $row['name'];
            $this->totals['totalCountServicesPerDist'] += $row['sum'];
            $this->totals['totalSumAmountPerDist'] += $row['total_amount'];

            // ведем подсчет по округу
            $this->totals['serviceNamesPerArea'][] = $row['name'];
            $this->totals['totalCountServicesPerArea'] += $row['sum'];
            $this->totals['totalSumAmountPerArea'] += $row['total_amount'];

            // ведем общий подсчет
            $this->totals['serviceNames'][] = $row['name'];
            $this->totals['totalCountServices'] += $row['sum'];
            $this->totals['totalSumAmount'] += $row['total_amount'];
        }

        if ($lastBatch === true && !empty($row)) {
            /*
             * Итог по последнему типу услуг
             */
            $this->renderTotalRow(
                $row['type_name'] . ': итого ' . $this->totals['totalPerType'],
                $this->totals['totalCountServicesPerType'],
                $this->totals['totalSumAmountPerType'],
                self::STYLE_TYPEGREY
            );

            /*
             * Итог по последней организации
             */
            $this->renderTotalRow(
                'Итого по организации ' . $this->totals['totalPerOrg'],
                $this->totals['totalCountServicesPerOrg'],
                $this->totals['totalSumAmountPerOrg'],
                self::STYLE_ORGGREY
            );

            /*
             * Итог по последнему району
             */
            $this->renderTotalRow(
                'Итого по району ' . count(array_unique($this->totals['serviceNamesPerDist'])),
                $this->totals['totalCountServicesPerDist'],
                $this->totals['totalSumAmountPerDist'],
                self::STYLE_DISTGREY
            );

            /*
             * Итог по последнему округу
             */
            $this->renderTotalRow(
                'Итого по округу ' . count(array_unique($this->totals['serviceNamesPerArea'])),
                $this->totals['totalCountServicesPerArea'],
                $this->totals['totalSumAmountPerArea'],
                self::STYLE_AREAGREY
            );

            /*
             * ВСЕГО
             */
            $this->renderTotalRow(
                'ВСЕГО ' . count(array_unique($this->totals['serviceNames'])),
                $this->totals['totalCountServices'],
                $this->totals['totalSumAmount'],
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

        $str = '<row r="' . $this->currentRow . '" spans="1:4">'
            . '<c r="A' . $this->currentRow . '" s="' . $style . '" t="str"><v>' . $title . '</v></c>'
            . '<c r="B' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="C' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="D' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
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
    private function renderTotalRow($typeName, $totalCountServicesPerType, $totalSumAmountPerType, $style)
    {
        $typeName = $this->cleanupCellValue($typeName);
        $totalCountServicesPerType = $this->cleanupCellValue($totalCountServicesPerType);
        $totalSumAmountPerType = $this->cleanupCellValue($totalSumAmountPerType);

        $str = '<row r="' . $this->currentRow . '" spans="1:4">'
            . '<c r="A' . $this->currentRow . '" s="' . $style . '" t="str"><v>' . $typeName . '</v></c>'
            . '<c r="B' . $this->currentRow . '" s="' . ($style + 1) . '" t="str"><v>' . $totalCountServicesPerType . '</v></c>'
            . '<c r="C' . $this->currentRow . '" s="' . ($style + 1) . '" />'
            . '<c r="D' . $this->currentRow . '" s="' . ($style + 1) . '" t="str"><v>' . $totalSumAmountPerType . '</v></c>'
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
        $sum = $this->cleanupCellValue(ArrayHelper::getValue($row, 'sum', ''));
        $price = $this->cleanupCellValue(ArrayHelper::getValue($row, 'price', ''));
        $total_amount = $this->cleanupCellValue(ArrayHelper::getValue($row, 'total_amount', ''));

        $str = '<row r="' . $this->currentRow . '" spans="1:4" customHeight="1" ht="30" outlineLevel="1">'
            . '<c r="A' . $this->currentRow . '" s="3" t="str"><v>' . $title . '</v></c>'
            . '<c r="B' . $this->currentRow . '"><v>' . $sum . '</v></c>'
            . '<c r="C' . $this->currentRow . '"><v>' . $price . '</v></c><c r="D' . $this->currentRow . '"><v>' . $total_amount . '</v></c>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;
    }
}
