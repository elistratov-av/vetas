<?php


namespace app\modules\adminv\models\excel;

/**
 * Отчет по уведомлениям
 *
 * @Class NotificationReportExport
 * @package app\modules\adminv\models\excel
 */
class NotificationReportExport extends AbstractReportExport
{
    public $species;
    /**
     * @var string
     */
    protected $maxColumn = 'L';
    /**
     * @var string
     */
    protected $filename = 'Сводный отчет по уведомлениям';
    /**
     * @var string
     */
    protected $cols = '<cols>'
    . '<col min="1" max="1" width="20.71" bestFit="true" customWidth="true"/>'
    . '<col min="2" max="2" width="19" customWidth="true"/>'
    . '<col min="3" max="3" width="19" customWidth="true"/>'
    . '<col min="4" max="4" width="19" customWidth="true"/>'
    . '<col min="5" max="5" width="19" customWidth="true"/>'
    . '<col min="6" max="6" width="19" customWidth="true"/>'
    . '<col min="7" max="7" width="13.71" customWidth="true"/>'
    . '<col min="8" max="8" width="13.71" customWidth="true"/>'
    . '<col min="9" max="9" width="13.71" customWidth="true" />'
    . '<col min="10" max="10" width="13.71" customWidth="true" />'
    . '<col min="11" max="11" width="13.71" customWidth="true" />'
    . '<col min="12" max="12" width="13.71" customWidth="true" />'
    . '</cols>';

    /**
     * @inheritDoc
     */
    protected function renderHead()
    {
        $this->mergeCells = '<mergeCell ref="A1:E1"/>'
            . '<mergeCell ref="B4:C4"/>'
            . '<mergeCell ref="A10:B10"/>'
            . '<mergeCell ref="C10:C11"/>'
            . '<mergeCell ref="D10:D11"/>'
            . '<mergeCell ref="E10:E11"/>'
            . '<mergeCell ref="F10:F11"/>'
            . '<mergeCell ref="G10:L10"/>';

        $sst = '<si><t>' . $this->title . '</t></si>';
        $this->writeLn($this->stringFile, $sst);

        $str = '<row r="' . $this->currentRow . '" spans="1:12" customHeight="1" ht="30">'
            . '<c r="A' . $this->currentRow . '" s="0" t="s"><v>0</v></c>'
            . '<c r="B' . $this->currentRow . '" s="0"/>'
            . '<c r="C' . $this->currentRow . '" s="0"/>'
            . '<c r="D' . $this->currentRow . '" s="0"/>'
            . '<c r="E' . $this->currentRow . '" s="0"/>'
            . '<c r="F' . $this->currentRow . '" s="0"/>'
            . '<c r="G' . $this->currentRow . '" s="0"/>'
            . '<c r="H' . $this->currentRow . '" s="0"/>'
            . '<c r="I' . $this->currentRow . '" s="0"/>'
            . '<c r="J' . $this->currentRow . '" s="0"/>'
            . '<c r="K' . $this->currentRow . '" s="0"/>'
            . '<c r="L' . $this->currentRow . '" s="0"/>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;

        $str = '<row r="1">'
            . '<c r="A1" s="1" t="str"><v>СВОДНЫЙ ОТЧЕТ ПО УВЕДОМЛЕНИЯМ</v></c>'
            . '</row>';
        $this->writeLn($this->sheetFile, $str);

        $str = '<row r="3">
                    <c r="A3" s="1" t="str"><v>Фильтры:</v></c>
                </row>';
        $this->writeLn($this->sheetFile, $str);

        $str = '<row r="4">'
            . '<c r="A4" s="0" t="str"><v>Период отчетности</v></c>'
            . '<c r="B4" s="0" t="str"><v>' . $this->from . ' - ' . $this->to . '</v></c>'
            . '<c r="E4" s="0" t="str"><v>Вид животного</v></c>'
            . '<c r="F4" s="0" t="str"><v>' . $this->species . '</v></c>'
            . '</row>';
        $this->writeLn($this->sheetFile, $str);

        $str = '<row r="10">
                    <c r="A10" s="2" t="str"><v>Напоминания о вакцинации</v></c>
                    <c r="B10" s="2" t="str"></c>
                    <c r="C10" s="2" t="str"><v>Напоминания о идентификации</v></c>
                    <c r="D10" s="2" t="s"><v>Уведомления о проведении противоэпизоотических мероприятий на местности</v></c>
                    <c r="E10" s="2" t="str"><v>Уведомления о найденном/отловленном владельческом животном</v></c>
                    <c r="F10" s="2" t="str"><v>Уведомления о готовности результатов исследований</v></c>
                    <c r="G10" s="2" t="str"><v>Нарушения</v></c>
                    <c r="H10" s="2" t="str"></c>
                    <c r="I10" s="2" t="str"></c>
                    <c r="J10" s="2" t="str"></c>
                    <c r="K10" s="2" t="str"></c>
                    <c r="L10" s="2" t="str"></c>
                </row>';
        $this->writeLn($this->sheetFile, $str);

        $str = '<row r="11">
                    <c r="A11" s="2" t="str"><v>Бешенство</v></c>
                    <c r="B11" s="2" t="str"><v>Лептоспироз</v></c>
                    <c r="C11" s="2" t="str"></c>
                    <c r="C11" s="2" t="str"></c>
                    <c r="E11" s="2" t="str"></c>
                    <c r="F11" s="2" t="str"></c>
                    <c r="G11" s="2" t="str"><v>Идентификации</v></c>
                    <c r="H11" s="2" t="str"><v>Вакцинации</v></c>
                    <c r="I11" s="2" t="str"><v>Правил карантина и других вет. сан. правил</v></c>
                    <c r="J11" s="2" t="str"><v>Сокрытие падежа или массового заболевания животных</v></c>
                    <c r="K11" s="2" t="str"><v>Правил / порядка провоза</v></c>
                    <c r="L11" s="2" t="str"><v>Правил обращения с биологическими отходами</v></c>
                </row>';
        $this->writeLn($this->sheetFile, $str);
    }

    /**
     * @inheritDoc
     */
    protected function renderBody()
    {
        $str = '<row r="12">'
            . '<c r="A12" s="3" t="str">' . '<v>' . $this->data['rabies'] . '</v></c>'
            . '<c r="B12" s="3" t="str">' . '<v>' . $this->data['lepto'] . '</v></c>'
            . '<c r="C12" s="3" t="str">' . '<v>' . $this->data['ident'] . '</v></c>'
            . '<c r="D12" s="3" t="str">' . '<v>' . $this->data['notif_qua'] . '</v></c>'
            . '<c r="E12" s="3" t="str">' . '<v>' . $this->data['found'] . '</v></c>'
            . '<c r="F12" s="3" t="str">' . '<v>' . $this->data['research'] . '</v></c>'
            . '<c r="G12" s="3" t="str">' . '<v>' . $this->data['no_ident'] . '</v></c>'
            . '<c r="H12" s="3" t="str">' . '<v>' . $this->data['no_vacc'] . '</v></c>'
            . '<c r="I12" s="3" t="str">' . '<v>' . $this->data['viol_qua'] . '</v></c>'
            . '<c r="J12" s="3" t="str">' . '<v>' . $this->data['death_disease'] . '</v></c>'
            . '<c r="K12" s="3" t="str">' . '<v>' . $this->data['transport'] . '</v></c>'
            . '<c r="L12" s="3" t="str">' . '<v>' . $this->data['bio_waste'] . '</v></c>'
            . '</row>';
        $this->writeLn($this->sheetFile, $str);
    }
}