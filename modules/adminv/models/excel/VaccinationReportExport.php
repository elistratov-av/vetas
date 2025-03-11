<?php

namespace app\modules\adminv\models\excel;

use yii\helpers\ArrayHelper;

/**
 * Отчет по охвату вакцинацией против бешенства
 *
 * Class VaccinationReportExport
 * @package app\modules\adminv\models\excel
 */
class VaccinationReportExport extends AbstractReportExport
{
    const STYLE_LIGHTGREY = 3;
    const STYLE_DARKGRAY = 5;

    /**
     * @var string
     */
    protected $filename = 'Отчет по охвату вакцинацией против бешенства c {from} по {to}';
    /**
     * @var string
     */
    protected $maxColumn = 'M';
    /**
     * @var string
     */
    protected $cols = '<cols>'
    . '<col min="1" max="1" width="40" customWidth="true" style="2"/>'
    . '<col min="2" max="2" width="12" customWidth="true" style="0"/>'
    . '<col min="3" max="3" width="12" customWidth="true" style="0"/>'
    . '<col min="4" max="4" width="14" customWidth="true" style="0"/>'
    . '<col min="5" max="5" width="13" customWidth="true" style="0"/>'
    . '<col min="6" max="6" width="12" customWidth="true" style="0"/>'
    . '<col min="7" max="7" width="12" customWidth="true" style="0"/>'
    . '<col min="8" max="8" width="14" customWidth="true" style="0"/>'
    . '<col min="9" max="9" width="13" customWidth="true" style="0"/>'
    . '<col min="10" max="10" width="12" customWidth="true" style="0"/>'
    . '<col min="11" max="11" width="12" customWidth="true" style="0"/>'
    . '<col min="12" max="12" width="14" customWidth="true" style="0"/>'
    . '<col min="13" max="13" width="13" customWidth="true" style="0"/>'
    . '</cols>';


    /**
     * @inheritDoc
     */
    protected function renderHead()
    {
        $this->mergeCells = '<mergeCell ref="A1:M1"/>'
            . '<mergeCell ref="A2:A3"/>'
            . '<mergeCell ref="B2:B3"/>'
            . '<mergeCell ref="C2:D2"/>'
            . '<mergeCell ref="E2:E3"/>'
            . '<mergeCell ref="F2:F3"/>'
            . '<mergeCell ref="G2:H2"/>'
            . '<mergeCell ref="I2:I3"/>'
            . '<mergeCell ref="J2:J3"/>'
            . '<mergeCell ref="K2:L2"/>'
            . '<mergeCell ref="M2:M3"/>';

        $sst = '<si><t>' . $this->title . '</t></si>';
        $this->writeLn($this->stringFile, $sst);

        $str = '<row r="' . $this->currentRow . '" spans="1:13" customHeight="1" ht="30">'
            . '<c r="A' . $this->currentRow . '" s="1" t="s"><v>0</v></c>'
            . '<c r="B' . $this->currentRow . '" s="1"/><c r="C1" s="1"/>'
            . '<c r="D' . $this->currentRow . '" s="1"/>'
            . '<c r="E' . $this->currentRow . '" s="1"/>'
            . '<c r="F' . $this->currentRow . '" s="1"/>'
            . '<c r="G' . $this->currentRow . '" s="1"/>'
            . '<c r="H' . $this->currentRow . '" s="1"/>'
            . '<c r="I' . $this->currentRow . '" s="1"/>'
            . '<c r="J' . $this->currentRow . '" s="1"/>'
            . '<c r="K' . $this->currentRow . '" s="1"/>'
            . '<c r="L' . $this->currentRow . '" s="1"/>'
            . '<c r="M' . $this->currentRow . '" s="1"/>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;

        $str = '<row r="' . $this->currentRow . '" spans="1:13">'
            . '<c r="A' . $this->currentRow . '" s="1" t="str"><v>Организация</v></c>'
            . '<c r="B' . $this->currentRow . '" s="1" t="str"><v>Кошек на учете</v></c>'
            . '<c r="C' . $this->currentRow . '" s="1" t="str"><v>Вакцинировано</v></c>'
            . '<c r="D' . $this->currentRow . '" s="1"/>'
            . '<c r="E' . $this->currentRow . '" s="1" t="str"><v>Отказ от вакцинации</v></c>'
            . '<c r="F' . $this->currentRow . '" s="1" t="str"><v>Собак на учете</v></c>'
            . '<c r="G' . $this->currentRow . '" s="1" t="str"><v>Вакцинировано</v></c>'
            . '<c r="H' . $this->currentRow . '" s="1"/>'
            . '<c r="I' . $this->currentRow . '" s="1" t="str"><v>Отказ от вакцинации</v></c>'
            . '<c r="J' . $this->currentRow . '" s="1" t="str"><v>Прочих животных на учете</v></c>'
            . '<c r="K' . $this->currentRow . '" s="1" t="str"><v>Вакцинировано</v></c>'
            . '<c r="L' . $this->currentRow . '" s="1"/>'
            . '<c r="M' . $this->currentRow . '" s="1" t="str"><v>Отказ от вакцинации</v></c>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;

        $str = '<row r="' . $this->currentRow . '" spans="1:13" customHeight="1" ht="55">'
            . '<c r="A' . $this->currentRow . '" s="1"/>'
            . '<c r="B' . $this->currentRow . '" s="1"/>'
            . '<c r="C' . $this->currentRow . '" s="1" t="str"><v>Вакциной Рабикан</v></c>'
            . '<c r="D' . $this->currentRow . '" s="1" t="str"><v>Комплексной вакциной</v></c>'
            . '<c r="E' . $this->currentRow . '" s="1"/>'
            . '<c r="F' . $this->currentRow . '" s="1"/>'
            . '<c r="G' . $this->currentRow . '" s="1" t="str"><v>Вакциной Рабикан</v></c>'
            . '<c r="H' . $this->currentRow . '" s="1" t="str"><v>Комплексной вакциной</v></c>'
            . '<c r="I' . $this->currentRow . '" s="1"/>'
            . '<c r="J' . $this->currentRow . '" s="1"/>'
            . '<c r="K' . $this->currentRow . '" s="1" t="str"><v>Вакциной Рабикан</v></c>'
            . '<c r="L' . $this->currentRow . '" s="1" t="str"><v>Комплексной вакциной</v></c>'
            . '<c r="M' . $this->currentRow . '" s="1"/>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;
    }

    /**
     * @inheritDoc
     */
    protected function renderBody()
    {
        $currentAreaId = null;
        $totalCatsPerArea = 0;
        $totalCatsRabicanPerArea = 0;
        $totalCatsComplexPerArea = 0;
        $totalCatsRejectedPerArea = 0;
        $totalDogsPerArea = 0;
        $totalDogsRabicanPerArea = 0;
        $totalDogsComplexPerArea = 0;
        $totalDogsRejectedPerArea = 0;
        $totalOtherPerArea = 0;
        $totalOtherRabicanPerArea = 0;
        $totalOtherComplexPerArea = 0;
        $totalOtherRejectedPerArea = 0;

        $totalCats = 0;
        $totalCatsRabican = 0;
        $totalCatsComplex = 0;
        $totalCatsRejected = 0;
        $totalDogs = 0;
        $totalDogsRabican = 0;
        $totalDogsComplex = 0;
        $totalDogsRejected = 0;
        $totalOther = 0;
        $totalOtherRabican = 0;
        $totalOtherComplex = 0;
        $totalOtherRejected = 0;

        foreach ($this->data as $row) {

            if ($currentAreaId != $row['id_area'] && $currentAreaId != null) {

                $this->renderTotalRow(
                    'Итого по округу',
                    $totalCatsPerArea,
                    $totalCatsRabicanPerArea,
                    $totalCatsComplexPerArea,
                    $totalCatsRejectedPerArea,
                    $totalDogsPerArea,
                    $totalDogsRabicanPerArea,
                    $totalDogsComplexPerArea,
                    $totalDogsRejectedPerArea,
                    $totalOtherPerArea,
                    $totalOtherRabicanPerArea,
                    $totalOtherComplexPerArea,
                    $totalOtherRejectedPerArea,
                    self::STYLE_LIGHTGREY
                );

                $totalCatsPerArea = 0;
                $totalCatsRabicanPerArea = 0;
                $totalCatsComplexPerArea = 0;
                $totalCatsRejectedPerArea = 0;
                $totalDogsPerArea = 0;
                $totalDogsRabicanPerArea = 0;
                $totalDogsComplexPerArea = 0;
                $totalDogsRejectedPerArea = 0;
                $totalOtherPerArea = 0;
                $totalOtherRabicanPerArea = 0;
                $totalOtherComplexPerArea = 0;
                $totalOtherRejectedPerArea = 0;
            }
            if ($currentAreaId != $row['id_area']) {
                $currentAreaId = $row['id_area'];
                $this->renderHeaderRow(
                    $row['name'] ?? self::NO_AREA,
                    self::STYLE_LIGHTGREY
                );
            }

            $this->renderRow($row);

            $totalCatsPerArea += $row['total_cats'];
            $totalCatsRabicanPerArea += $row['total_rabies_cats'];
            $totalCatsComplexPerArea += $row['total_complex_cats'];
            $totalCatsRejectedPerArea += $row['total_reject_cats'];
            $totalDogsPerArea += $row['total_dogs'];
            $totalDogsRabicanPerArea += $row['total_rabies_dogs'];
            $totalDogsComplexPerArea += $row['total_complex_dogs'];
            $totalDogsRejectedPerArea += $row['total_reject_dogs'];
            $totalOtherPerArea += $row['total_other'];
            $totalOtherRabicanPerArea += $row['total_rabies_other'];
            $totalOtherComplexPerArea += $row['total_complex_other'];
            $totalOtherRejectedPerArea += $row['total_reject_other'];

            $totalCats += $row['total_cats'];
            $totalCatsRabican += $row['total_rabies_cats'];
            $totalCatsComplex += $row['total_complex_cats'];
            $totalCatsRejected += $row['total_reject_cats'];
            $totalDogs += $row['total_dogs'];
            $totalDogsRabican += $row['total_rabies_dogs'];
            $totalDogsComplex += $row['total_complex_dogs'];
            $totalDogsRejected += $row['total_reject_dogs'];
            $totalOther += $row['total_other'];
            $totalOtherRabican += $row['total_rabies_other'];
            $totalOtherComplex += $row['total_complex_other'];
            $totalOtherRejected += $row['total_reject_other'];
        }

        /*
         * Итог по последнему округу
         */
        $this->renderTotalRow(
            'Итого по округу',
            $totalCatsPerArea,
            $totalCatsRabicanPerArea,
            $totalCatsComplexPerArea,
            $totalCatsRejectedPerArea,
            $totalDogsPerArea,
            $totalDogsRabicanPerArea,
            $totalDogsComplexPerArea,
            $totalDogsRejectedPerArea,
            $totalOtherPerArea,
            $totalOtherRabicanPerArea,
            $totalOtherComplexPerArea,
            $totalOtherRejectedPerArea,
            self::STYLE_LIGHTGREY
        );

        /*
         * ВСЕГО
         */
        $this->renderTotalRow(
            'ВСЕГО',
            $totalCats,
            $totalCatsRabican,
            $totalCatsComplex,
            $totalCatsRejected,
            $totalDogs,
            $totalDogsRabican,
            $totalDogsComplex,
            $totalDogsRejected,
            $totalOther,
            $totalOtherRabican,
            $totalOtherComplex,
            $totalOtherRejected,
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

        $str = '<row r="' . $this->currentRow . '" spans="1:13">'
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
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;
    }

    /**
     * @param string $title
     * @param int    $totalCats
     * @param int    $totalCatsRabican
     * @param int    $totalCatsComplex
     * @param int    $totalCatsRejected
     * @param int    $totalDogs
     * @param int    $totalDogsRabican
     * @param int    $totalDogsComplex
     * @param int    $totalDogsRejected
     * @param int    $totalOther
     * @param int    $totalOtherRabican
     * @param int    $totalOtherComplex
     * @param int    $totalOtherRejected
     * @param int    $style
     */
    private function renderTotalRow(
        $title,
        $totalCats,
        $totalCatsRabican,
        $totalCatsComplex,
        $totalCatsRejected,
        $totalDogs,
        $totalDogsRabican,
        $totalDogsComplex,
        $totalDogsRejected,
        $totalOther,
        $totalOtherRabican,
        $totalOtherComplex,
        $totalOtherRejected,
        $style
    )
    {
        $title = $this->cleanupCellValue($title);

        $str = '<row r="' . $this->currentRow . '" spans="1:13" customHeight="1" ht="30">'
            . '<c r="A' . $this->currentRow . '" s="' . $style . '" t="str"><v>' . $title . '</v></c>'
            . '<c r="B' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalCats . '</v></c>'
            . '<c r="C' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalCatsRabican . '</v></c>'
            . '<c r="D' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalCatsComplex . '</v></c>'
            . '<c r="E' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalCatsRejected . '</v></c>'
            . '<c r="F' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalDogs . '</v></c>'
            . '<c r="G' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalDogsRabican . '</v></c>'
            . '<c r="H' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalDogsComplex . '</v></c>'
            . '<c r="I' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalDogsRejected . '</v></c>'
            . '<c r="J' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalOther . '</v></c>'
            . '<c r="K' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalOtherRabican . '</v></c>'
            . '<c r="L' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalOtherComplex . '</v></c>'
            . '<c r="M' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalOtherRejected . '</v></c>'
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

        $total_cats = ArrayHelper::getValue($row, 'total_cats', 0);
        $total_rabies_cats = ArrayHelper::getValue($row, 'total_rabies_cats', 0);
        $total_complex_cats = ArrayHelper::getValue($row, 'total_complex_cats', 0);
        $total_reject_cats = ArrayHelper::getValue($row, 'total_reject_cats', 0);
        $total_dogs = ArrayHelper::getValue($row, 'total_dogs', 0);
        $total_rabies_dogs = ArrayHelper::getValue($row, 'total_rabies_dogs', 0);
        $total_complex_dogs = ArrayHelper::getValue($row, 'total_complex_dogs', 0);
        $total_reject_dogs = ArrayHelper::getValue($row, 'total_reject_dogs', 0);
        $total_other = ArrayHelper::getValue($row, 'total_other', 0);
        $total_rabies_other = ArrayHelper::getValue($row, 'total_rabies_other', 0);
        $total_complex_other = ArrayHelper::getValue($row, 'total_complex_other', 0);
        $total_reject_other = ArrayHelper::getValue($row, 'total_reject_other', 0);

        $str = '<row r="' . $this->currentRow . '" spans="1:13" customHeight="1" ht="30">'
            . '<c r="A' . $this->currentRow . '" s="2" t="str"><v>' . $title . '</v></c>'
            . '<c r="B' . $this->currentRow . '"><v>' . $total_cats . '</v></c>'
            . '<c r="C' . $this->currentRow . '"><v>' . $total_rabies_cats . '</v></c>'
            . '<c r="D' . $this->currentRow . '"><v>' . $total_complex_cats . '</v></c>'
            . '<c r="E' . $this->currentRow . '"><v>' . $total_reject_cats . '</v></c>'
            . '<c r="F' . $this->currentRow . '"><v>' . $total_dogs . '</v></c>'
            . '<c r="G' . $this->currentRow . '"><v>' . $total_rabies_dogs . '</v></c>'
            . '<c r="H' . $this->currentRow . '"><v>' . $total_complex_dogs . '</v></c>'
            . '<c r="I' . $this->currentRow . '"><v>' . $total_reject_dogs . '</v></c>'
            . '<c r="J' . $this->currentRow . '"><v>' . $total_other . '</v></c>'
            . '<c r="K' . $this->currentRow . '"><v>' . $total_rabies_other . '</v></c>'
            . '<c r="L' . $this->currentRow . '"><v>' . $total_complex_other . '</v></c>'
            . '<c r="M' . $this->currentRow . '"><v>' . $total_reject_other . '</v></c>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;
    }
}
