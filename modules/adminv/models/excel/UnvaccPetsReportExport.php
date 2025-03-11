<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 10.09.20
 * Time: 17:11
 */

namespace app\modules\adminv\models\excel;


use yii\db\Query;
use yii\helpers\ArrayHelper;

class UnvaccPetsReportExport extends AbstractReportExport
{
    const STYLE_AREAGREY = 4;
    const STYLE_DISTGREY = 6;
    const STYLE_TOTALGRAY = 10;

    /**
     * @var string
     */
    protected $filename = 'Отчет по невакцинированным животным на {to}';
    /**
     * @var string
     */
    protected $maxColumn = 'H';
    /**
     * @var string
     */
    protected $cols = '<cols>'
    . '<col min="1" max="1" width="20" customWidth="true" style="1"/>'
    . '<col min="4" max="4" width="30" customWidth="true" style="1"/>'
    . '<col min="5" max="5" width="40" customWidth="true" style="1"/>'
    . '<col min="2" max="2" width="21" customWidth="true" style="0"/>'
    . '<col min="3" max="3" width="20" customWidth="true" style="0"/>'
    . '<col min="6" max="6" width="25" customWidth="true" style="0"/>'
    . '<col min="7" max="7" width="15" customWidth="true" style="0"/>'
    . '<col min="8" max="8" width="20" customWidth="true" style="0"/>'
    . '<col min="9" max="9" width="20" customWidth="true" style="0"/>'
    . '<col min="10" max="10" width="20" customWidth="true" style="0"/>'
    . '<col min="11" max="11" width="20" customWidth="true" style="0"/>'
    . '<col min="12" max="12" width="20" customWidth="true" style="0"/>'
    . '<col min="13" max="13" width="20" customWidth="true" style="0"/>'
    . '<col min="14" max="14" width="20" customWidth="true" style="0"/>'
    . '<col min="15" max="15" width="20" customWidth="true" style="0"/>'
    . '<col min="16" max="16" width="20" customWidth="true" style="0"/>'
    . '</cols>';

    /**
     * @inheritDoc
     */
    protected function renderHead()
    {
        $this->mergeCells = '<mergeCell ref="A1:P1"/>';

        $sst = '<si><t>' . $this->title . '</t></si>';
        $this->writeLn($this->stringFile, $sst);

        $str = '<row r="' . $this->currentRow . '" spans="1:16" customHeight="1" ht="30">'
            . '<c r="A' . $this->currentRow . '" s="2" t="s"><v>0</v></c>'
            . '<c r="B' . $this->currentRow . '" s="2"/>'
            . '<c r="C' . $this->currentRow . '" s="2"/>'
            . '<c r="D' . $this->currentRow . '" s="3"/>'
            . '<c r="E' . $this->currentRow . '" s="3"/>'
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

        $this->mergeCells = '<mergeCell ref="A2:A3"/>';
        $this->mergeCells = '<mergeCell ref="B2:B3"/>';
        $this->mergeCells = '<mergeCell ref="C2:C3"/>';
        $this->mergeCells = '<mergeCell ref="D2:D3"/>';
        $this->mergeCells = '<mergeCell ref="E2:E3"/>';
        $this->mergeCells = '<mergeCell ref="F2:F3"/>';
        $this->mergeCells = '<mergeCell ref="G2:G3"/>';
        $this->mergeCells = '<mergeCell ref="H2:H3"/>';
        $this->mergeCells = '<mergeCell ref="I2:I3"/>';
        $this->mergeCells = '<mergeCell ref="J2:J3"/>';
        $this->mergeCells = '<mergeCell ref="K2:K3"/>';
        $this->mergeCells = '<mergeCell ref="L2:L3"/>';
        $this->mergeCells = '<mergeCell ref="M2:M3"/>';
        $this->mergeCells = '<mergeCell ref="N2:N3"/>';
        $this->mergeCells = '<mergeCell ref="O2:P2"/>';
        $str = '<row r="' . $this->currentRow . '" spans="1:16" customHeight="1" ht="50">'
            . '<c r="A' . $this->currentRow . '" s="3" t="str"><v>№ п\п</v></c>'
            . '<c r="B' . $this->currentRow . '" s="3" t="str"><v>Административный округ</v></c>'
            . '<c r="C' . $this->currentRow . '" s="3" t="str"><v>Район</v></c>'
            . '<c r="D' . $this->currentRow . '" s="3" t="str"><v>ФИО владельца</v></c>'
            . '<c r="E' . $this->currentRow . '" s="3" t="str"><v>Адрес регистрации</v></c>'
            . '<c r="F' . $this->currentRow . '" s="3" t="str"><v>Адрес фактического проживания</v></c>'
            . '<c r="G' . $this->currentRow . '" s="3" t="str"><v>Телефон</v></c>'
            . '<c r="H' . $this->currentRow . '" s="3" t="str"><v>E-mail</v></c>'
            . '<c r="I' . $this->currentRow . '" s="3" t="str"><v>Вид животного</v></c>'
            . '<c r="J' . $this->currentRow . '" s="3" t="str"><v>Порода</v></c>'
            . '<c r="K' . $this->currentRow . '" s="3" t="str"><v>Пол</v></c>'
            . '<c r="L' . $this->currentRow . '" s="3" t="str"><v>Идентификационный номер</v></c>'
            . '<c r="M' . $this->currentRow . '" s="3" t="str"><v>Регистрационный номер</v></c>'
            . '<c r="N' . $this->currentRow . '" s="3" t="str"><v>Кличка</v></c>'
            . '<c r="O' . $this->currentRow . '" s="3" t="str"><v>Дата последней вакцинации</v></c>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;

        $str = '<row r="' . $this->currentRow . '" spans="1:16" customHeight="1" ht="50">'
            . '<c r="O' . $this->currentRow . '" s="3" t="str"><v>Бешенство</v></c>'
            . '<c r="P' . $this->currentRow . '" s="3" t="str"><v>Лептоспироз</v></c>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;

    }

    /**
     * @inheritDoc
     */
    protected function renderBody()
    {
        $currentDistId = false;
        $currentAreaId = false;
        $totals = [
            'totalPerDist' => 0,
            'totalPerArea' => 0,
            'total' => 0,
        ];

        foreach ($this->data as $batch) {
            foreach ($batch as $row) {
                // если данные по текущему району кончились, выводим итог
                if ($currentDistId != $row['id_district'] && $currentDistId != false) {
                    $this->renderTotalRow(
                        'Итого по району ' . $totals['totalPerDist'],
                        self::STYLE_DISTGREY
                    );
                    $currentDistId = false;
                    $totals['totalPerDist'] = 0;
                }

                // если данные в текущем округе кончились, выводим итог
                if ($currentAreaId != $row['id_area'] && $currentAreaId != false) {
                    $this->renderTotalRow(
                        'Итого по округу ' . $totals['totalPerArea'],
                        self::STYLE_AREAGREY
                    );
                    $currentAreaId = false;
                    $totals['totalPerArea'] = 0;
                }

                // если начался новый округ, выводим его название
                if ($currentAreaId !== $row['id_area']) {
                    $currentAreaId = $row['id_area'];
                }

                // если начался новый район, выводим его название
                if ($currentDistId !== $row['id_district']) {
                    $currentDistId = $row['id_district'];
                }

                $this->renderRow($row);

                // ведем подсчет
                foreach ($totals as &$total) {
                    $total++;
                }
            }
        }

        /*
         * Итог по последнему району
         */
        $this->renderTotalRow(
            'Итого по району ' . $totals['totalPerDist'],
            self::STYLE_DISTGREY
        );

        /*
         * Итог по последнему округу
         */
        $this->renderTotalRow(
            'Итого по округу ' . $totals['totalPerArea'],
            self::STYLE_AREAGREY
        );

        /*
         * ВСЕГО
         */
        $this->renderTotalRow(
            'ВСЕГО ' . $totals['total'],
            self::STYLE_TOTALGRAY
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
            . '<c r="D' . $this->currentRow . '" s="' . $style . '"/>'
            . '<c r="E' . $this->currentRow . '" s="' . $style . '"/>'
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
     * @param int    $style
     */
    private function renderTotalRow($title, $style)
    {
        $this->mergeCells .= '<mergeCell ref="A' . $this->currentRow . ':' . $this->maxColumn . $this->currentRow . '"/>';

        $title = $this->cleanupCellValue($title);

        $str = '<row r="' . $this->currentRow . '" spans="1:8">'
            . '<c r="A' . $this->currentRow . '" s="' . $style . '" t="str"><v>' . $title . '</v></c>'
            . '<c r="B' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="C' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="D' . $this->currentRow . '" s="' . $style . '"/>'
            . '<c r="E' . $this->currentRow . '" s="' . $style . '"/>'
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
     * @param array $row
     */
    private function renderRow($row)
    {
        $title = $this->cleanupCellValue(ArrayHelper::getValue($row, 'number', ''));

        $area = $this->cleanupCellValue(ArrayHelper::getValue($row, 'area_name', ''));
        $dist = $this->cleanupCellValue(ArrayHelper::getValue($row, 'dist_name', ''));
        $fullname = $this->cleanupCellValue(ArrayHelper::getValue($row, 'owner_name', ''));
        $reg_address = $this->cleanupCellValue(ArrayHelper::getValue($row, 'reg_address', ''));
        $fact_address = $this->cleanupCellValue(ArrayHelper::getValue($row, 'fact_address', ''));
        $phone = $this->cleanupCellValue(ArrayHelper::getValue($row, 'owner_phone', ''));
        $mail = $this->cleanupCellValue(ArrayHelper::getValue($row, 'owner_mail', ''));
        $species = $this->cleanupCellValue(ArrayHelper::getValue($row, 'species', ''));
        $breed = $this->cleanupCellValue(ArrayHelper::getValue($row, 'breed', ''));
        $sex = $this->cleanupCellValue(ArrayHelper::getValue($row, 'sex', ''));
        $pet_ident = $this->cleanupCellValue(ArrayHelper::getValue($row, 'pet_ident', ''));
        $reg_num = $this->cleanupCellValue(ArrayHelper::getValue($row, 'reg_num', ''));
        $pet_name = $this->cleanupCellValue(ArrayHelper::getValue($row, 'pet_name', ''));
        $rab_date = $this->cleanupCellValue(ArrayHelper::getValue($row, 'rab_date', ''));
        $lept_date = $this->cleanupCellValue(ArrayHelper::getValue($row, 'lept_date', ''));

        $str = '<row r="' . $this->currentRow . '" spans="1:8" customHeight="1" ht="30">'
            . '<c r="A' . $this->currentRow . '" s="8" t="str"><v>' . $title . '</v></c>'
            . '<c r="B' . $this->currentRow . '" s="9" t="str"><v>' . $area . '</v></c>'
            . '<c r="C' . $this->currentRow . '" s="9" t="str"><v>' . $dist . '</v></c>'
            . '<c r="D' . $this->currentRow . '" s="8" t="str"><v>' . $fullname . '</v></c>'
            . '<c r="E' . $this->currentRow . '" s="8" t="str"><v>' . $reg_address . '</v></c>'
            . '<c r="F' . $this->currentRow . '" s="9" t="str"><v>' . $fact_address . '</v></c>'
            . '<c r="G' . $this->currentRow . '" s="9" t="str"><v>' . $phone . '</v></c>'
            . '<c r="H' . $this->currentRow . '" s="9" t="str"><v>' . $mail . '</v></c>'
            . '<c r="I' . $this->currentRow . '" s="9" t="str"><v>' . $species . '</v></c>'
            . '<c r="J' . $this->currentRow . '" s="9" t="str"><v>' . $breed . '</v></c>'
            . '<c r="K' . $this->currentRow . '" s="9" t="str"><v>' . $sex . '</v></c>'
            . '<c r="L' . $this->currentRow . '" s="9" t="str"><v>' . $pet_ident . '</v></c>'
            . '<c r="M' . $this->currentRow . '" s="9" t="str"><v>' . $reg_num . '</v></c>'
            . '<c r="N' . $this->currentRow . '" s="9" t="str"><v>' . $pet_name . '</v></c>'
            . '<c r="O' . $this->currentRow . '" s="9" t="str"><v>' . $rab_date . '</v></c>'
            . '<c r="P' . $this->currentRow . '" s="9" t="str"><v>' . $lept_date . '</v></c>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;
    }
}