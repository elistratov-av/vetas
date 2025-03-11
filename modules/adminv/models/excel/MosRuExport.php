<?php

namespace app\modules\adminv\models\excel;

use yii\helpers\ArrayHelper;

/**
 * Статистика mos.ru
 *
 * Class MosRuExport
 * @package app\modules\adminv\models\excel
 */
class MosRuExport extends AbstractReportExport
{
    /**
     * @var array
     */
    public $organizations;
    /**
     * @var string
     */
    protected $filename = 'Статистика mos.ru c {from} по {to}';
    /**
     * @var string
     */
    protected $maxColumn = 'K';
    /**
     * @var string
     */
    protected $cols = '<cols>'
    . '<col min="1" max="1" width="55.272217" bestFit="true" customWidth="true" style="3"/>'
    . '<col min="2" max="2" width="7" customWidth="true" style="0"/>'
    . '<col min="3" max="3" width="13" customWidth="true" style="0"/>'
    . '<col min="4" max="4" width="13" customWidth="true" style="0"/>'
    . '<col min="5" max="5" width="15" customWidth="true" style="0"/>'
    . '<col min="6" max="6" width="10" customWidth="true" style="0"/>'
    . '<col min="7" max="7" width="10" customWidth="true" style="0"/>'
    . '<col min="8" max="8" width="10" customWidth="true" style="0"/>'
    . '<col min="9" max="9" width="10" customWidth="true" style="0"/>'
    . '<col min="10" max="10" width="10" customWidth="true" style="0"/>'
    . '<col min="11" max="11" width="10" customWidth="true" style="0"/>'
    . '</cols>';

    /**
     * @inheritDoc
     */
    protected function renderHead()
    {
        $this->mergeCells = '<mergeCell ref="A1:K1"/>'
            . '<mergeCell ref="D2:E2"/>'
            . '<mergeCell ref="F2:H2"/>'
            . '<mergeCell ref="I2:K2"/>';

        $sst = '<si><t>' . $this->title . '</t></si>';
        $this->writeLn($this->stringFile, $sst);

        $str = '<row r="' . $this->currentRow . '" spans="1:11" customHeight="1" ht="30">'
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
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;

        $str = '<row r="2" spans="1:11">'
            . '<c r="A2" s="2"/>'
            . '<c r="B2" s="2"/>'
            . '<c r="C2" s="2"/>'
            . '<c r="D2" s="2" t="str"><v>Отменено</v></c>'
            . '<c r="E2" s="2"/>'
            . '<c r="F2" s="2" t="str"><v>Записи для</v></c>'
            . '<c r="G2" s="2"/>'
            . '<c r="H2" s="2"/>'
            . '<c r="I2" s="2" t="str"><v>Проведено приемов для</v></c>'
            . '<c r="J2" s="2"/>'
            . '<c r="K2" s="2"/>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;

        $str = '<row r="' . $this->currentRow . '" spans="1:11">'
            . '<c r="A' . $this->currentRow . '" s="2" t="str"><v>Организация</v></c>'
            . '<c r="B' . $this->currentRow . '" s="2" t="str"><v>Всего</v></c>'
            . '<c r="C' . $this->currentRow . '" s="2" t="str"><v>Перенесено</v></c>'
            . '<c r="D' . $this->currentRow . '" s="2" t="str"><v>владельцем</v></c>'
            . '<c r="E' . $this->currentRow . '" s="2" t="str"><v>организацией</v></c>'
            . '<c r="F' . $this->currentRow . '" s="2" t="str"><v>кошек</v></c>'
            . '<c r="G' . $this->currentRow . '" s="2" t="str"><v>собак</v></c>'
            . '<c r="H' . $this->currentRow . '" s="2" t="str"><v>иные</v></c>'
            . '<c r="I' . $this->currentRow . '" s="2" t="str"><v>кошек</v></c>'
            . '<c r="J' . $this->currentRow . '" s="2" t="str"><v>собак</v></c>'
            . '<c r="K' . $this->currentRow . '" s="2" t="str"><v>иные</v></c>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;
    }

    /**
     * @inheritDoc
     */
    protected function renderBody()
    {
        foreach ($this->data as $row) {
            $title = $this->organizations[$row['id_organization']] ?? 'Неизвестная организация #' . $row['id_organization'];
            $this->renderRow($title, $row);
        }
    }

    /**
     * @param string $title
     * @param array  $row
     */
    private function renderRow($title, $row)
    {
        $title = $this->cleanupCellValue($title);

        $total = ArrayHelper::getValue($row, 'total', 0);
        $moved = ArrayHelper::getValue($row, 'moved', 0);
        $canceled_by_owner = ArrayHelper::getValue($row, 'canceled_by_owner', 0);
        $canceled_by_org = ArrayHelper::getValue($row, 'canceled_by_org', 0);
        $cats_visits = ArrayHelper::getValue($row, 'cats_visits', 0);
        $dogs_visits = ArrayHelper::getValue($row, 'dogs_visits', 0);
        $other_visits = ArrayHelper::getValue($row, 'other_visits', 0);
        $cats_finished_visits = ArrayHelper::getValue($row, 'cats_finished_visits', 0);
        $dogs_finished_visits = ArrayHelper::getValue($row, 'dogs_finished_visits', 0);
        $other_finished_visits = ArrayHelper::getValue($row, 'other_finished_visits', 0);

        $str = '<row r="' . $this->currentRow . '" spans="1:11">
            <c r="A' . $this->currentRow . '" s="3" t="str"><v>' . $title . '</v></c>
            <c r="B' . $this->currentRow . '"><v>' . $total . '</v></c>
            <c r="C' . $this->currentRow . '"><v>' . $moved . '</v></c>
            <c r="D' . $this->currentRow . '"><v>' . $canceled_by_owner . '</v></c>
            <c r="E' . $this->currentRow . '"><v>' . $canceled_by_org . '</v></c>
            <c r="F' . $this->currentRow . '"><v>' . $cats_visits . '</v></c>
            <c r="G' . $this->currentRow . '"><v>' . $dogs_visits . '</v></c>
            <c r="H' . $this->currentRow . '"><v>' . $other_visits . '</v></c>
            <c r="I' . $this->currentRow . '"><v>' . $cats_finished_visits . '</v></c>
            <c r="J' . $this->currentRow . '"><v>' . $dogs_finished_visits . '</v></c>
            <c r="K' . $this->currentRow . '"><v>' . $other_finished_visits . '</v></c>
        </row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;
    }
}
