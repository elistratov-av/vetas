<?php

namespace app\modules\v2\modules\reports\models;

use app\modules\adminv\models\excel\AbstractReportExport;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use yii\helpers\FileHelper;

/**
 * Журнал приемов
 *
 * Class VisitJournalReportExport
 *
 * @package app\modules\adminv\models\excel
 * @see https://confluence.altarix.ru/confluence/pages/viewpage.action?pageId=134644332
 */
class VisitJournalReportExport extends AbstractReportExport
{
    public $columns = [];

    /**
     * @inheritDoc
     */
    public function init(): void
    {
        $this->filename = uniqid('journal_', false);
        $this->from = '';
        $this->to = '';

        if (!count($this->columns)) {
            $this->cols = '<cols/>';

            return;
        }
        $this->cols = '<cols>';
        foreach ($this->columns as $idx => $omit) {
            $nr = $idx + 1;
            $this->cols .= "<col min=\"{$nr}\" max=\"{$nr}\" width=\"25\" customWidth=\"true\" style=\"0\"/>";
        }
        $this->cols .= '</cols>';
        parent::init();
    }


    /**
     * @inheritDoc
     */
    protected function renderHead(): void
    {
        $sst = '<si><t>' . $this->title . '</t></si>';
        $this->writeLn($this->stringFile, $sst);

        if (!count($this->columns)) {
            return;
        }

        // Some magic
        $str = ['<row r="' . $this->currentRow . '" spans="1:' . count($this->columns) . '">'];
        $column = 'A';
        foreach ($this->columns as $idx => ['label' => $name]) {
            $offset = $column . $this->currentRow;
            $str[] = '<c r="' . $offset . '" t="str"><v>' . htmlspecialchars($name, ENT_XML1) . '</v></c>';
            $column++;
        }
        $str[] = '</row>';
        $this->writeLn($this->sheetFile, implode('', $str));
        $this->currentRow++;
    }

    /**
     * @inheritDoc
     */
    protected function renderBody(): void
    {
        $props = [];
        foreach ($this->columns as ['prop_path' => $prop]) {
            $props[] = $prop;
        }
        $rowSize = '1:' . count($props);
        foreach ($this->query->batch() as $batch) {
            foreach (VisitJournalModel::addVisitDescriptions($batch) as $r) {
                $column = 'A';
                $str = "<row r=\"{$this->currentRow}\" spans=\"{$rowSize}\">";
                foreach ($props as $prop) {
                    $offset = $column . $this->currentRow;
                    $column++;
                    $str .= '<c r="' . $offset . '"';
                    $v = array_key_exists($prop, $r) ? (string)$r[$prop] : '';
                    if (!$v) {
                        $str .= '/>';
                        continue;
                    }
                    if (is_numeric($v)) {
                        $str .= "><v>$v</v></c>";
                        continue;
                    }
                    $str .= ' t="str"><v>' . htmlspecialchars($v, ENT_XML1) . '</v></c>';
                }
                $str .= '</row>';
                $this->writeLn($this->sheetFile, $str);
                $this->currentRow++;
            }
        }
    }

    /**
     * Форматируем созданый документ
     */
    private function formatDocument()
    {
        $spreadsheet = (new ReaderXlsx())->load($this->tempfile);
        $sheet = $spreadsheet->getActiveSheet();

        $col = 'A';
        $i = count($this->columns)-1;
        while($i){
            $col++;
            $i--;
        }

        $sheet
            ->getStyle('A1:' . $col . (--$this->currentRow))
            ->applyFromArray(
                [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_HAIR,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                    'font' => [
                        'size' => 10,
                    ]
                ]
            );

        $sheet
            ->getStyle('A1:' . $col . '1')
            ->getFont()
            ->setBold(true);

        foreach ($spreadsheet->getActiveSheet()->getColumnIterator() as $column) {
            $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($this->tempfile);
    }


    protected function createExcelFile()
    {
        parent::createExcelFile();
        $this->formatDocument();
    }



    /**
     * @inheritDoc
     */
    protected function send()
    {
        $rs = \Yii::$app->getResponse();
        $name = $this->filename;

        try {
            $rs->getHeaders()
                ->set('Access-Control-Expose-Headers', '*')
                ->set('Content-Disposition', 'attachment; filename="' . $name . '"')
                ->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');

            $rs->sendContentAsFile(base64_encode(file_get_contents($this->tempfile)), $name, [
                'mimeType' => FileHelper::getMimeTypeByExtension($name),
            ]);
        } finally {
            FileHelper::unlink($this->tempfile);
            FileHelper::removeDirectory($this->tempdir);
        }
    }
}
