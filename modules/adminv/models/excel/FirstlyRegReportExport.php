<?php

namespace app\modules\adminv\models\excel;

use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * Отчет по первично зарегистрированным владельцам/животным
 *
 * Class FirstlyRegReportExport
 * @package app\modules\adminv\models\excel
 */
class FirstlyRegReportExport extends AbstractReportExport
{
    const STYLE_AREAGREY = 6;
    const STYLE_DISTGREY = 10;
    const STYLE_ORGGREY = 14;
    const STYLE_SPECIESGREY = 18;
    const STYLE_TOTALGRAY = 22;

    /**
     * @var string
     */
    protected $filename = 'Отчет по первично зарегистрированным владельцам/животным c {from} по {to}';
    /**
     * @var string
     */
    protected $maxColumn = 'F';
    /**
     * @var string
     */
    protected $cols = '<cols>'
    . '<col min="1" max="1" width="50" customWidth="true" style="0"/>'
    . '<col min="2" max="2" width="40" customWidth="true" style="1"/>'
    . '<col min="3" max="3" width="20" customWidth="true" style="4"/>'
    . '<col min="4" max="4" width="20" customWidth="true" style="4"/>'
    . '<col min="5" max="5" width="20" customWidth="true" style="4"/>'
    . '<col min="6" max="6" width="20" customWidth="true" style="5"/>'
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
    /**
     * @var array
     */
    private $rows = [];

    /**
     * @inheritDoc
     */
    protected function renderHead()
    {
        $this->mergeCells = '<mergeCell ref="A1:F1"/>';

        $sst = '<si><t>' . $this->title . '</t></si>';
        $this->writeLn($this->stringFile, $sst);

        $str = '<row r="' . $this->currentRow . '" spans="1:6" customHeight="1" ht="30">'
            . '<c r="A' . $this->currentRow . '" t="s" s="2"><v>' . $this->currentStr . '</v></c>'
            . '<c r="B' . $this->currentRow . '" s="3"/>'
            . '<c r="C' . $this->currentRow . '" s="2"/>'
            . '<c r="D' . $this->currentRow . '" s="2"/>'
            . '<c r="E' . $this->currentRow . '" s="2"/>'
            . '<c r="F' . $this->currentRow . '" s="2"/>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;

        $str = '<row r="' . $this->currentRow . '" spans="1:6" customHeight="1" ht="50">'
            . '<c r="A' . $this->currentRow . '" s="3" t="str"><v>Владелец (ФЛ, ЮЛ)</v></c>'
            . '<c r="B' . $this->currentRow . '" s="3" t="str"><v>Адрес владельца</v></c>'
            . '<c r="C' . $this->currentRow . '" s="3" t="str"><v>Телефон владельца</v></c>'
            . '<c r="D' . $this->currentRow . '" s="3" t="str"><v>Кличка животного</v></c>'
            . '<c r="E' . $this->currentRow . '" s="3" t="str"><v>Тип метки (основной идентификатор животного)</v></c>'
            . '<c r="F' . $this->currentRow . '" s="3" t="str"><v>Значение метки</v></c>'
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
        $query->select(new Expression('COUNT(*)'));
        $query->orderBy([]);
        $total = $query->scalar();

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
            // идем поэлементно по массиву с результатами запроса
            // если данные по текущему виду животных кончились, выводим итог
            if ($this->currentSpeciesId !== $row['idSpec']
                && $this->currentSpeciesId !== false
                || ($this->currentOrgId !== $row['id_reg_organization']
                    || $this->currentDistId !== $row['id_district']
                    || $this->currentAreaId !== $row['id_area'])
                && $this->currentSpeciesId !== false) {
                $this->renderTotalRow(
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
                $this->renderTotalRow(
                    'Итого по организации ' . $this->totals['totalPerOrg'],
                    self::STYLE_ORGGREY
                );
                $this->currentOrgId = false;
                $this->currentSpeciesId = false;
                $this->totals['totalPerOrg'] = 0;
            }

            // если данные в текущем районе кончились, выводим итог
            if ($this->currentDistId !== $row['id_district'] && $this->currentDistId !== false) {
                $this->renderTotalRow(
                    'Итого по району ' . $this->totals['totalPerDist'],
                    self::STYLE_DISTGREY
                );
                $this->currentSpeciesId = false;
                $this->currentOrgId = false;
                $this->totals['totalPerDist'] = 0;
            }

            // если данные в текущем округе кончились, выводим итог
            if ($this->currentAreaId !== $row['id_area'] && $this->currentAreaId !== false) {
                $this->renderTotalRow(
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
                $this->renderHeaderRow(
                    $row['areaName'] ?? self::NO_AREA,
                    self::STYLE_AREAGREY
                );
            }

            // если начался новый район, выводим его название
            if ($this->currentDistId !== $row['id_district']) {
                $this->currentDistId = $row['id_district'];
                $this->renderHeaderRow(
                    $row['distName'] ?? self::NO_DISTRICT,
                    self::STYLE_DISTGREY
                );
            }

            // если началась новая орагнизация, выводим ее название
            if ($this->currentOrgId !== $row['id_reg_organization']) {
                $this->currentOrgId = $row['id_reg_organization'];
                $this->renderHeaderRow(
                    $row['short_name'],
                    self::STYLE_ORGGREY
                );
            }

            // если начался новый вид животных, выводим его название
            if ($this->currentSpeciesId !== $row['idSpec']) {
                $this->currentSpeciesId = $row['idSpec'];
                $this->renderHeaderRow(
                    $row['specName'],
                    self::STYLE_SPECIESGREY
                );
            }

            $this->renderRow($row);

            // ведем подсчет
            foreach ($this->totals as &$total) {
                $total++;
            }
        }

        if ($lastBatch === true) {
            // Итог по последнему виду животных
            $this->renderTotalRow(
                'Итого по виду ' . $this->totals['totalPerSpec'],
                self::STYLE_SPECIESGREY
            );
            // Итог по последней организации
            $this->renderTotalRow(
                'Итого по организации ' . $this->totals['totalPerOrg'],
                self::STYLE_ORGGREY
            );
            // Итог по последнему району
            $this->renderTotalRow(
                'Итого по району ' . $this->totals['totalPerDist'],
                self::STYLE_DISTGREY
            );
            // Итог по последнему округу
            $this->renderTotalRow(
                'Итого по округу ' . $this->totals['totalPerArea'],
                self::STYLE_AREAGREY
            );
            // ВСЕГО
            $this->renderTotalRow(
                'ВСЕГО ' . $this->totals['total'],
                self::STYLE_TOTALGRAY
            );
        }
    }

    /**
     * @param string $title
     * @param int    $style
     */
    private function renderHeaderRow($title, $style)
    {
        $title = $this->cleanupCellValue($title);

        $str = '<row r="' . $this->currentRow . '" spans="1:6">'
            . '<c r="A' . $this->currentRow . '" s="' . $style . '" t="str"><v>' . $title . '</v></c>'
            . '<c r="B' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="C' . $this->currentRow . '" s="' . ($style + 2) . '"/>'
            . '<c r="D' . $this->currentRow . '" s="' . ($style + 2) . '"/>'
            . '<c r="E' . $this->currentRow . '" s="' . ($style + 2) . '"/>'
            . '<c r="F' . $this->currentRow . '" s="' . ($style + 3) . '"/>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;
    }

    /**
     * @param string $title
     * @param int    $style
     */
    private function renderTotalRow($title, $style)
    {
        $title = $this->cleanupCellValue($title);

        $str = '<row r="' . $this->currentRow . '" spans="1:6">'
            . '<c r="A' . $this->currentRow . '" s="' . $style . '" t="str"><v>' . $title . '</v></c>'
            . '<c r="B' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="C' . $this->currentRow . '" s="' . ($style + 2) . '"/>'
            . '<c r="D' . $this->currentRow . '" s="' . ($style + 2) . '"/>'
            . '<c r="E' . $this->currentRow . '" s="' . ($style + 2) . '"/>'
            . '<c r="F' . $this->currentRow . '" s="' . ($style + 3) . '"/>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;
    }

    /**
     * @param array $row
     */
    private function renderRow($row)
    {
        $ownName = $this->cleanupCellValue(ArrayHelper::getValue($row, 'ownName', ''));
        $ownAddress = $this->cleanupCellValue(ArrayHelper::getValue($row, 'ownAddress', ''));
        $ownPhone = $this->cleanupCellValue(ArrayHelper::getValue($row, 'ownPhone', ''));
        $petName = $this->cleanupCellValue(ArrayHelper::getValue($row, 'petName', ''));
        $identType = $this->cleanupCellValue(ArrayHelper::getValue($row, 'identType', ''));
        $identification_code = $this->cleanupCellValue(ArrayHelper::getValue($row, 'identification_code', ''));

        $str = '<row r="' . $this->currentRow . '" spans="1:6" customHeight="1" ht="30">'
            . '<c r="A' . $this->currentRow . '" t="str"><v>' . $ownName . '</v></c>'
            . '<c r="B' . $this->currentRow . '" s="1" t="str"><v>' . $ownAddress . '</v></c>'
            . '<c r="C' . $this->currentRow . '" s="4" t="str"><v>' . $ownPhone . '</v></c>'
            . '<c r="D' . $this->currentRow . '" s="4" t="str"><v>' . $petName . '</v></c>'
            . '<c r="E' . $this->currentRow . '" s="4" t="str" ><v>' . $identType . '</v></c>'
            . '<c r="F' . $this->currentRow . '" s="5" t="str"><v>' . $identification_code . '</v></c>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;
    }
}
