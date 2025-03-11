<?php

namespace app\modules\adminv\models\excel;

/**
 * Отчет о пиковых часах загруженности
 *
 * Class RushHoursReportExport
 * @package app\modules\adminv\models\excel
 */
class RushHoursReportExport extends AbstractReportExport
{
    /**
     * @var string
     */
    protected $filename = 'Отчет о пиковых часах загруженности c {from} по {to}';
    /**
     * @var string
     */
    protected $title = 'Количество обращений по времени суток за период c {from} по {to}';
    /**
     * @var string
     */
    protected $maxColumn = 'D';
    /**
     * @var string
     */
    protected $cols = '<cols>'
    . '<col min="1" max="1" width="16.424561" bestFit="true" customWidth="true" style="3"/>'
    . '<col min="2" max="2" width="15" customWidth="true" style="0"/>'
    . '</cols>';

    /**
     * @inheritDoc
     */
    protected function renderHead()
    {
        $this->mergeCells = '<mergeCell ref="A1:D1"/>';

        $sst = '<si><t>' . $this->title . '</t></si>';
        $this->writeLn($this->stringFile, $sst);

        $str = '<row r="' . $this->currentRow . '" spans="1:4" customHeight="1" ht="60">'
            . '<c r="A' . $this->currentRow . '" t="s" s="2"><v>' . $this->currentStr . '</v></c>'
            . '<c r="B' . $this->currentRow . '" s="2"/><c r="C' . $this->currentRow . '" s="1"/>'
            . '<c r="D' . $this->currentRow . '" s="1"/></row>';
        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;

        $this->currentStr++;
        $sst = '<si><t>Час</t></si>';
        $this->writeLn($this->stringFile, $sst);

        $str = '<row r="' . $this->currentRow . '" spans="1:4" customHeight="1" ht="60">'
            . '<c r="A' . $this->currentRow . '" t="s" s="2"><v>' . $this->currentStr . '</v></c>';

        $this->currentStr++;
        $sst = '<si><t>Количество обращений</t></si>';
        $this->writeLn($this->stringFile, $sst);

        $str .= ('<c r="B' . $this->currentRow . '" t="s" s="2"><v>' . $this->currentStr . '</v></c>'
            . '<c r="C' . $this->currentRow . '" s="1"/><c r="D' . $this->currentRow . '" s="1"/>'
            . '</row>');
        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;
    }

    /**
     * @inheritDoc
     */
    protected function renderBody()
    {
        foreach ($this->data as $hour => $value) {
            $nextHour = ($hour == 23) ? 0 : $hour + 1;
            $title = $hour . ':01 - ' . $nextHour . ':00';

            $this->currentStr++;
            $sst = '<si><t>' . $title . '</t></si>';
            $this->writeLn($this->stringFile, $sst);

            $str = '<row r="' . $this->currentRow . '" spans="1:4">'
                . '<c r="A' . $this->currentRow . '" s="3" t="s"><v>' . $this->currentStr . '</v></c>'
                . '<c r="B' . $this->currentRow . '"><v>' . $value . '</v></c>'
                . '</row>';
            $this->writeLn($this->sheetFile, $str);
            $this->currentRow++;
        }
    }
}
