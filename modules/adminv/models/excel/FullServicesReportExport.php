<?php

namespace app\modules\adminv\models\excel;

use app\models\db\Visits;
use yii\helpers\ArrayHelper;

/**
 * Сводный отчет по услугам
 *
 * Class FullServicesReportExport
 * @package app\modules\adminv\models\excel
 */
class FullServicesReportExport extends AbstractReportExport
{
    const STYLE_LIGHTGREY = 6;
//    const STYLE_MIDGREY = 4;
//    const STYLE_DARKGRAY = 8;

    /**
     * @var string
     */
    protected $filename = 'Сводный отчет по услугам c {from} по {to}';
    /**
     * @var string
     */
    protected $title = 'Сводный отчет по услугам за период c {from} по {to}';
    /**
     * @var string
     */
    protected $maxColumn = 'H';

    protected $countVisits;
    protected $countNew;
    protected $countWork;

    /**
     * @var string
     */
    protected $cols = '<cols>'
    . '<col min="1" max="1" width="30" customWidth="true" style="1"/>'
    . '<col min="2" max="2" width="9" customWidth="true" style="0"/>'
    . '<col min="6" max="6" width="9" customWidth="true" style="0"/>'
    . '<col min="10" max="10" width="9" customWidth="true" style="0"/>'
    . '<col min="14" max="14" width="9" customWidth="true" style="0"/>'
    . '<col min="3" max="3" width="10" customWidth="true" style="0"/>'
    . '<col min="4" max="4" width="12" customWidth="true" style="0"/>'
    . '<col min="5" max="5" width="12" customWidth="true" style="0"/>'
    . '</cols>';

    /**
     * @inheritDoc
     */
    protected function renderHead()
    {
        $this->countVisits = Visits::find()
            ->count();
        $this->countNew = Visits::find()
            ->andWhere(['status' => 'N'])
            ->count();
        $this->countWork = Visits::find()
            ->andWhere(['status' => 'W'])
            ->count();

        $this->mergeCells = '<mergeCell ref="A1:H1"/>';
//            . '<mergeCell ref="A4:A5"/>'
//            . '<mergeCell ref="B4:B5"/>'
//            . '<mergeCell ref="C4:C5"/>'
//            . '<mergeCell ref="D4:H5"/>';

        $sst = '<si><t>' . $this->title . '</t></si>';
        $this->writeLn($this->stringFile, $sst);
        $this->currentRow++;

//        var_dump($countVisits); die();

        $this->mergeCells = '<mergeCell ref="A2:B2"/>'
            . '<mergeCell ref="C2:D2"/>'
            . '<mergeCell ref="E2:F2"/>';

        $str = '<row r="' . $this->currentRow . '" spans="1:8" customHeight="1" ht="30">'
            . '<c r="A' . $this->currentRow . '" s="2"/>'
            . '<c r="B' . $this->currentRow . '" s="2" t="s"><v>' . $this->countVisits . '</v></c>'
            . '<c r="C' . $this->currentRow . '" s="2"/>'
            . '<c r="D' . $this->currentRow . '" s="2" t="s"><v>' . $this->countNew . '</v></c>'
            . '<c r="E' . $this->currentRow . '" s="2"/>'
            . '<c r="F' . $this->currentRow . '" s="2" t="s"><v>' . $this->countWork . '</v></c>'
            . '<c r="G' . $this->currentRow . '" s="2"/>'
            . '<c r="H' . $this->currentRow . '" s="2"/>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;
        $this->currentRow++;

        $str = '<row r="' . $this->currentRow . '" spans="1:8" customHeight="1" ht="30">'
            . '<c r="A' . $this->currentRow . '" s="2" t="s"><v>0</v></c>'
            . '<c r="B' . $this->currentRow . '" s="2"/>'
            . '<c r="C' . $this->currentRow . '" s="2"/>'
            . '<c r="D' . $this->currentRow . '" s="2"/>'
            . '<c r="E' . $this->currentRow . '" s="2"/>'
            . '<c r="F' . $this->currentRow . '" s="2"/>'
            . '<c r="G' . $this->currentRow . '" s="2"/>'
            . '<c r="H' . $this->currentRow . '" s="2"/>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;

        $str = '<row r="' . $this->currentRow . '" spans="1:8">'
            . '<c r="A' . $this->currentRow . '" s="3" t="s"><v>Год</v></c>'
            . '<c r="B' . $this->currentRow . '" s="3" t="s"><v>Месяц</v></c>'
            . '<c r="C' . $this->currentRow . '" s="3" t="s"><v>Название услуги</v></c>'
            . '<c r="D' . $this->currentRow . '" s="3" t="s"><v>Количество записей</v></c>'
            . '<c r="E' . $this->currentRow . '" s="3"/>'
            . '<c r="F' . $this->currentRow . '" s="3"/>'
            . '<c r="G' . $this->currentRow . '" s="3"/>'
            . '<c r="H' . $this->currentRow . '" s="3"/>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;

        $str = '<row r="' . $this->currentRow . '" spans="1:8">'
            . '<c r="A' . $this->currentRow . '" s="3"/>'
            . '<c r="B' . $this->currentRow . '" s="3"/>'
            . '<c r="C' . $this->currentRow . '" s="3"/>'
            . '<c r="D' . $this->currentRow . '" s="3" t="str"><v>Запись по ЖО</v></c>'
            . '<c r="E' . $this->currentRow . '" s="3" t="str"><v>Запись по телефону</v></c>'
            . '<c r="F' . $this->currentRow . '" s="3" t="str"><v>Запись по направлению</v></c>'
            . '<c r="G' . $this->currentRow . '" s="3" t="str"><v>Запись на портале mos.ru</v></c>'
            . '<c r="H' . $this->currentRow . '" s="3" t="str"><v>Запись в мобильном приложении</v></c>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;
    }

    /**
     * @inheritDoc
     */
    protected function renderBody()
    {
//        $currentAreaId = false;
//        $currentDistId = false;
        $totals = [
            'totalLQ' => 0,
            'totalPhone' => 0,
            'totalWorkday' => 0,
            'totalMosRu' => 0,
            'totalMPGU' => 0,
        ];
        $months = [
            '01' => 'Январь',
            '02' => 'Февраль',
            '03' => 'Март',
            '04' => 'Апрель',
            '05' => 'Май',
            '06' => 'Июнь',
            '07' => 'Июль',
            '08' => 'Август',
            '09' => 'Сентябрь',
            '10' => 'Октябрь',
            '11' => 'Ноябрь',
            '12' => 'Декабрь',
        ];

        foreach ($this->data as $row) {

            $this->renderRow($row);

            $totals['totalLQ'] += $row['total_lq'];
            $totals['totalPhone'] += $row['total_phone'];
            $totals['totalWorkday'] += $row['total_workday'];
            $totals['totalMosRu'] += $row['total_mosru'];
            $totals['totalMPGU'] += $row['total_mpgu'];
        }

        /*
         * ВСЕГО
         */
        $this->renderTotalRow(
            'ВСЕГО',
            $totals['totalLQ'],
            $totals['totalPhone'],
            $totals['totalWorkday'],
            $totals['totalMosRu'],
            $totals['totalMPGU'],
            self::STYLE_LIGHTGREY
        );
    }

    /**
     * @param string $title
     * @param int    $totalMosruCreated
     * @param int    $totalMosruCancelled
     * @param int    $totalMosruTransfered
     * @param int    $totalMosruFinished
     * @param int    $totalPhoneCreated
     * @param int    $totalPhoneCancelled
     * @param int    $totalPhoneTransfered
     * @param int    $totalPhoneFinished
     * @param int    $totalLqCreated
     * @param int    $totalLqCancelled
     * @param int    $totalLqTransfered
     * @param int    $totalLqFinished
     * @param int    $totalWdCreated
     * @param int    $totalWdCancelled
     * @param int    $totalWdFinished
     * @param int    $style
     */
    private function renderTotalRow(
        $title,
        $totalLQ,
        $totalPhone,
        $totalWorkday,
        $totalMosRu,
        $totalMPGU,
        $style
    )
    {
        $title = $this->cleanupCellValue($title);

        $str = '<row r="' . $this->currentRow . '" spans="1:8">'
            . '<c r="A' . $this->currentRow . '" s="' . $style . '" t="str"><v>' . $title . '</v></c>'
            . '<c r="B' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="C' . $this->currentRow . '" s="' . ($style + 1) . '"/>'
            . '<c r="D' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalLQ . '</v></c>'
            . '<c r="E' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalPhone . '</v></c>'
            . '<c r="F' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalWorkday . '</v></c>'
            . '<c r="G' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalMosRu . '</v></c>'
            . '<c r="H' . $this->currentRow . '" s="' . ($style + 1) . '"><v>' . $totalMPGU . '</v></c>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;
    }

    /**
     * @param array $row
     */
    private function renderRow($row)
    {
//        $title = empty($row['short_name']) ? self::NO_ORGANIZATION : $this->cleanupCellValue($row['short_name']);

        $year = substr(ArrayHelper::getValue($row, 'date', 0), 0, 4);
        $month = substr(ArrayHelper::getValue($row, 'date', 0), -2);
        $service = ArrayHelper::getValue($row, 'service_name', 0);
        $totalLq = ArrayHelper::getValue($row, 'total_lq', 0);
        $totalPhone = ArrayHelper::getValue($row, 'total_phone', 0);
        $totalWorkday = ArrayHelper::getValue($row, 'total_workday', 0);
        $totalMosRu = ArrayHelper::getValue($row, 'total_mosru', 0);
        $totalMPGU = ArrayHelper::getValue($row, 'total_mpgu', 0);

        $str = '<row r="' . $this->currentRow . '" spans="1:8" customHeight="1" ht="15">'
            . '<c r="A' . $this->currentRow . '" s="1" t="str"><v>' . $year . '</v></c>'
            . '<c r="B' . $this->currentRow . '"><v>' . $month . '</v></c>'
            . '<c r="C' . $this->currentRow . '"><v>' . $service . '</v></c>'
            . '<c r="D' . $this->currentRow . '"><v>' . $totalLq . '</v></c>'
            . '<c r="E' . $this->currentRow . '"><v>' . $totalPhone . '</v></c>'
            . '<c r="F' . $this->currentRow . '"><v>' . $totalWorkday . '</v></c>'
            . '<c r="G' . $this->currentRow . '"><v>' . $totalMosRu . '</v></c>'
            . '<c r="H' . $this->currentRow . '"><v>' . $totalMPGU . '</v></c>'
            . '</row>';

        $this->writeLn($this->sheetFile, $str);
        $this->currentRow++;
    }
}
