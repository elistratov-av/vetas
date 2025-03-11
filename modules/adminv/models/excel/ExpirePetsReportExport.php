<?php

namespace app\modules\adminv\models\excel;

use yii\helpers\ArrayHelper;

/**
 * Отчет о снятии с учета
 *
 * Class ExpirePetsReportExport
 * @package app\modules\adminv\models\excel
 */
class ExpirePetsReportExport extends AbstractReportExport
{
    const STYLE_ORGGREY = 4;
    const STYLE_SPECIESGREY = 6;
    const STYLE_TOTALGRAY = 10;

    /**
     * @var string
     */
    protected $filename = 'Отчет о снятии с учета c {from} по {to}';
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
    . '<col min="8" max="8" width="20" customWidth="true" style="1"/>'
    . '<col min="2" max="2" width="21" customWidth="true" style="0"/>'
    . '<col min="3" max="3" width="20" customWidth="true" style="0"/>'
    . '<col min="6" max="6" width="25" customWidth="true" style="0"/>'
    . '<col min="7" max="7" width="15" customWidth="true" style="0"/>'
    . '</cols>';

    /**
     * @inheritDoc
     */
    protected function renderHead()
    {
        $this->mergeCells = '<mergeCell ref="A1:H1"/>';

        $sst = '<si><t>' . $this->title . '</t></si>';
        $this->writeLn($this->stringFile, $sst);

        $str = '<row r="' . $this->currentRow . '" spans="1:8" customHeight="1" ht="30">'
            . '<c r="A' . $this->currentRow . '" s="2" t="s"><v>0</v></c>'
            . '<c r="B' . $this->currentRow . '" s="2"/>'
            . '<c r="C' . $this->currentRow . '" s="2"/>'
            . '<c r="D' . $this->currentRow . '" s="3"/>'
            . '<c r="E' . $this->currentRow . '" s="3"/>'
            . '<c r="F' . $this->currentRow . '" s="2"/>'
            . '<c r="G' . $this->currentRow . '" s="2"/>'
            . '<c r="H' . $this->currentRow . '" s="3"/>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;

        $str = '<row r="' . $this->currentRow . '" spans="1:8" customHeight="1" ht="50">'
            . '<c r="A' . $this->currentRow . '" s="3" t="str"><v>Кличка</v></c>'
            . '<c r="B' . $this->currentRow . '" s="3" t="str"><v>Тип метки (основной идентификатор животного)</v></c>'
            . '<c r="C' . $this->currentRow . '" s="3" t="str"><v>Значение метки</v></c>'
            . '<c r="D' . $this->currentRow . '" s="3" t="str"><v>Владелец (ФЛ, ЮЛ)</v></c>'
            . '<c r="E' . $this->currentRow . '" s="3" t="str"><v>Адрес владельца (ФЛ, ЮЛ)</v></c>'
            . '<c r="F' . $this->currentRow . '" s="3" t="str"><v>Телефон владельца (ФЛ, ЮЛ)</v></c>'
            . '<c r="G' . $this->currentRow . '" s="3" t="str"><v>Дата снятия с учета</v></c>'
            . '<c r="H' . $this->currentRow . '" s="3" t="str"><v>Причина снятия с учета</v></c>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;
    }

    /**
     * @inheritDoc
     */
    protected function renderBody()
    {
        $currentOrgId = false;
        $currentSpeciesId = false;
        $totals = [
            'totalPerSpec' => 0,
            'totalPerOrg' => 0,
            'total' => 0,
        ];

        foreach ($this->data as $row) {
            // если данные по текущему виду животных кончились, выводим итог
            if (($currentSpeciesId != $row['id_species']
                && $currentSpeciesId != false
                || (($currentOrgId != $row['id_reg_organization']))
                && $currentSpeciesId != false)) {
                $this->renderTotalRow(
                    'Итого по виду ' . $totals['totalPerSpec'],
                    self::STYLE_SPECIESGREY
                );
                $currentSpeciesId = false;
                $totals['totalPerSpec'] = 0;
            }

            // если данные в текущей организации кончились, выводим итог
            if ($currentOrgId != $row['id_reg_organization']
                && $currentOrgId != false) {
                $this->renderTotalRow(
                    'Итого по организации ' . $totals['totalPerOrg'],
                    self::STYLE_ORGGREY
                );
                $currentOrgId = false;
                $currentSpeciesId = false;
                $totals['totalPerOrg'] = 0;
            }

            // если началась новая орагнизация, выводим ее название
            if ($currentOrgId !== $row['id_reg_organization']) {
                $currentOrgId = $row['id_reg_organization'];
                $this->renderHeaderRow(
                    $row['short_name'] ?? self::NO_ORGANIZATION,
                    self::STYLE_ORGGREY
                );
            }

            // если начался новый вид животных, выводим его название
            if ($currentSpeciesId !== $row['id_species']) {
                $currentSpeciesId = $row['id_species'];
                $this->renderHeaderRow(
                    $row['spec_name'],
                    self::STYLE_SPECIESGREY
                );
            }

            $this->renderRow($row);

            // ведем подсчет
            foreach ($totals as &$total) {
                $total++;
            }
        }

        /*
         * Итог по последнему виду животных
         */
        $this->renderTotalRow(
            'Итого по виду ' . $totals['totalPerSpec'],
            self::STYLE_SPECIESGREY
        );

        /*
         * Итог по последней организации
         */
        $this->renderTotalRow(
            'Итого по организации ' . $totals['totalPerOrg'],
            self::STYLE_ORGGREY
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

        $str = '<row r="' . $this->currentRow . '" spans="1:8">'
            . '<c r="A' . $this->currentRow . '" s="' . $style . '" t="str"><v>' . $title . '</v></c>'
            . '<c r="B' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="C' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="D' . $this->currentRow . '" s="' . $style . '"/>'
            . '<c r="E' . $this->currentRow . '" s="' . $style . '"/>'
            . '<c r="F' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="G' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="H' . $this->currentRow . '" s="' . $style . '"/>'
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
            . '<c r="H' . $this->currentRow . '" s="' . $style . '"/>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;
    }

    /**
     * @param array $row
     */
    private function renderRow($row)
    {
        $title = $this->cleanupCellValue(ArrayHelper::getValue($row, 'pet_name', ''));

        $ident_name = $this->cleanupCellValue(ArrayHelper::getValue($row, 'ident_name', ''));
        $identification_code = $this->cleanupCellValue(ArrayHelper::getValue($row, 'identification_code', ''));
        $fullname = $this->cleanupCellValue(ArrayHelper::getValue($row, 'fullname', ''));
        $full_address = $this->cleanupCellValue(ArrayHelper::getValue($row, 'full_address', ''));
        $contact_name = $this->cleanupCellValue(ArrayHelper::getValue($row, 'contact_name', ''));
        $reg_expire_date = $this->cleanupCellValue(ArrayHelper::getValue($row, 'reg_expire_date', ''));
        $reason_name = $this->cleanupCellValue(ArrayHelper::getValue($row, 'reason_name', ''));

        $str = '<row r="' . $this->currentRow . '" spans="1:8" customHeight="1" ht="30">'
            . '<c r="A' . $this->currentRow . '" s="8" t="str"><v>' . $title . '</v></c>'
            . '<c r="B' . $this->currentRow . '" s="9" t="str"><v>' . $ident_name . '</v></c>'
            . '<c r="C' . $this->currentRow . '" s="9" t="str"><v>' . $identification_code . '</v></c>'
            . '<c r="D' . $this->currentRow . '" s="8" t="str"><v>' . $fullname . '</v></c>'
            . '<c r="E' . $this->currentRow . '" s="8" t="str"><v>' . $full_address . '</v></c>'
            . '<c r="F' . $this->currentRow . '" s="9" t="str"><v>' . $contact_name . '</v></c>'
            . '<c r="G' . $this->currentRow . '" s="9" t="str"><v>' . $reg_expire_date . '</v></c>'
            . '<c r="H' . $this->currentRow . '" s="8" t="str"><v>' . $reason_name . '</v></c>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;
    }
}
