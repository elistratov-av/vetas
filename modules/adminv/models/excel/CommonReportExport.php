<?php

namespace app\modules\adminv\models\excel;

use yii\helpers\ArrayHelper;

/**
 * Краткая статистика организаций по регистрации и приемам
 *
 * Class CommonReportExport
 * @package app\modules\adminv\models\excel
 */
class CommonReportExport extends AbstractReportExport
{
    const STYLE_LIGHTGREY = 4;
    const STYLE_DARKGRAY = 6;

    /**
     * @var string
     */
    protected $filename = 'Kratkaya statistika organizatsiy po registratsii i priemam s {from} po {to}';
    /**
     * @var string
     */
    protected $title = 'Краткая статистика организаций по регистрации и приемам с {from} по {to}';
    /**
     * @var string
     */
    protected $maxColumn = 'E';
    /**
     * @var string
     */
    protected $cols = '<cols>'
    . '<col min="1" max="1" width="55.272217" bestFit="true" customWidth="true" style="3"/>'
    . '<col min="2" max="2" width="20" customWidth="true" style="0"/>'
    . '<col min="3" max="3" width="25" customWidth="true" style="0"/>'
    . '<col min="4" max="4" width="16" customWidth="true" style="0"/>'
    . '<col min="5" max="5" width="21" customWidth="true" style="0"/>'
    . '</cols>';

    /**
     * @inheritDoc
     */
    protected function renderHead()
    {
        $this->mergeCells = '<mergeCell ref="A1:E1"/>';

        $sst = '<si><t>' . $this->title . '</t></si>';
        $this->writeLn($this->stringFile, $sst);

        $str = '<row r="' . $this->currentRow . '" spans="1:5" customHeight="1" ht="30">'
            . '<c r="A' . $this->currentRow . '" s="1" t="s"><v>0</v></c>'
            . '<c r="B' . $this->currentRow . '" s="1"/>'
            . '<c r="C' . $this->currentRow . '" s="1"/>'
            . '<c r="D' . $this->currentRow . '" s="1"/>'
            . '<c r="E' . $this->currentRow . '" s="1"/>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;

        $str = '<row r="' . $this->currentRow . '" spans="1:5" customHeight="1" ht="30">'
            . '<c r="A' . $this->currentRow . '" s="2" t="str"><v>Организация</v></c>'
            . '<c r="B' . $this->currentRow . '" s="2" t="str"><v>Зарегистрировано животных всего</v></c>'
            . '<c r="C' . $this->currentRow . '" s="2" t="str"><v>Зарегистрировано животных за период</v></c>'
            . '<c r="D' . $this->currentRow . '" s="2" t="str"><v>Приемов всего</v></c>'
            . '<c r="E' . $this->currentRow . '" s="2" t="str"><v>Приемов за период</v></c>'
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
        $totalPetsPerArea = 0;
        $totalPeriodPetsPerArea = 0;
        $totalVisitsPerArea = 0;
        $totalPeriodVisitsPerArea = 0;
        $totalPets = 0;
        $totalPeriodPets = 0;
        $totalVisits = 0;
        $totalPeriodVisits = 0;

        foreach ($this->data as $row) {
            // если данные в текущем округе кончились, выводим итог
            if($currentAreaId !== $row['id_area'] && $currentAreaId !== false) {
                $this->renderTotalRow(
                    'Итого по округу',
                    $totalPetsPerArea,
                    $totalPeriodPetsPerArea,
                    $totalVisitsPerArea,
                    $totalPeriodVisitsPerArea,
                    self::STYLE_LIGHTGREY
                );
                $currentAreaId = false;
                $totalPetsPerArea = 0;
                $totalPeriodPetsPerArea = 0;
                $totalVisitsPerArea = 0;
                $totalPeriodVisitsPerArea = 0;
            }

            // если начался новый округ, выводим его название
            if($currentAreaId !== $row['id_area']) {
                $currentAreaId = $row['id_area'];
                $this->renderHeaderRow(
                    $row['name'] ?? self::NO_AREA,
                    self::STYLE_LIGHTGREY
                );
            }

            $this->renderRow($row);

            // ведем подсчет по округу
            $totalPetsPerArea += $row['total_pets'];
            $totalPeriodPetsPerArea += $row['period_pets'];
            $totalVisitsPerArea += $row['total_visits'];
            $totalPeriodVisitsPerArea += $row['period_visits'];

            // ведем общий подсчет
            $totalPets += $row['total_pets'];
            $totalPeriodPets += $row['period_pets'];
            $totalVisits += $row['total_visits'];
            $totalPeriodVisits += $row['period_visits'];
        }

        /*
         * Итог по последнему округу
         */
        $this->renderTotalRow(
            'Итого по округу',
            $totalPetsPerArea,
            $totalPeriodPetsPerArea,
            $totalVisitsPerArea,
            $totalPeriodVisitsPerArea,
            self::STYLE_LIGHTGREY
        );

        /*
         * ВСЕГО
         */
        $this->renderTotalRow(
            'ВСЕГО',
            $totalPets,
            $totalPeriodPets,
            $totalVisits,
            $totalPeriodVisits,
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

        $str = '<row r="' . $this->currentRow . '" spans="1:5">'
            . '<c r="A' . $this->currentRow . '" s="' . $style . '" t="str"><v>' . $title . '</v></c>'
            . '<c r="B' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="C' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="D' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="E' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;
    }

    /**
     * @param string $title
     * @param int    $totalPets
     * @param int    $totalPeriodPets
     * @param int    $totalVisits
     * @param int    $totalPeriodVisits
     * @param int    $style
     */
    private function renderTotalRow($title, $totalPets, $totalPeriodPets, $totalVisits, $totalPeriodVisits, $style)
    {
        $title = $this->cleanupCellValue($title);

        $str = '<row r="' . $this->currentRow . '" spans="1:5">'
            . '<c r="A' . $this->currentRow . '" s="' . $style . '" t="str"><v>' . $title . '</v></c>'
            . '<c r="B' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalPets . '</v></c>'
            . '<c r="C' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalPeriodPets . '</v></c>'
            . '<c r="D' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalVisits . '</v></c>'
            . '<c r="E' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalPeriodVisits . '</v></c>'
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

        $totalPets = ArrayHelper::getValue($row, 'total_pets', 0);
        $periodPets = ArrayHelper::getValue($row, 'period_pets', 0);
        $totalVisits = ArrayHelper::getValue($row, 'total_visits', 0);
        $periodVisits = ArrayHelper::getValue($row, 'period_visits', 0);

        $str = '<row r="' . $this->currentRow . '" spans="1:5">'
            . '<c r="A' . $this->currentRow . '" s="3" t="str"><v>' . $title . '</v></c>'
            . '<c r="B' . $this->currentRow . '"><v>' . $totalPets . '</v></c>'
            . '<c r="C' . $this->currentRow . '"><v>' . $periodPets . '</v></c>'
            . '<c r="D' . $this->currentRow . '"><v>' . $totalVisits . '</v></c>'
            . '<c r="E' . $this->currentRow . '"><v>' . $periodVisits . '</v></c>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;
    }
}
