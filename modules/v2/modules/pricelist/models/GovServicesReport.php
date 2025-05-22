<?php

namespace app\modules\v2\modules\pricelist\models;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use yii\helpers\FileHelper;
use yii\web\ServerErrorHttpException;
use Ramsey\Uuid\Uuid;
use yii\base\BaseObject;
use yii\web\Response;

/**
 * Отчет услуги прайслиста
 */
class GovServicesReport extends BaseObject
{
       /**
     * @var array
     */
    public $data;
    /**
     * @var \yii\db\ActiveQuery
     */
    public $query;

    /**
     * @var string
     */
    protected $filename;
    /**
     * @var string
     */
    protected $title;
    /**
     * @var string
     */
    protected $tempfile;
    /**
     * @var string
     */
    protected $tempdir;
    /**
     * @var string
     */
    protected $sheetFile;
    /**
     * @var string
     */
    protected $stringFile;
    /**
     * @var int
     */
    protected $currentRow = 0;
    /**
     * @var int
     */
    protected $currentStr = 0;
    /**
     * @var string
     */
    protected $maxColumn;
    /**
     * @var string
     */
    protected $mergeCells = '';
    /**
     * @var string
     */
    protected $cols = '';    
    
    public $columns = [];

    public function init(): void
    {
        $this->filename = uniqid('gs_', false);

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

        if (!extension_loaded('zip')) {
            throw new \Exception('Не установлено расширение ZIP');
        }

        $this->title = $this->filename;
        $this->filename = $this->filename . '.xlsx';

        $tempname = Uuid::uuid4()->toString();
        $this->tempdir = FileHelper::normalizePath(\Yii::getAlias('@runtime') . '/excel-reports/' . $tempname);

        if (!FileHelper::createDirectory($this->tempdir)) {
            throw new ServerErrorHttpException('Не удалось создать временную папку');
        }

        FileHelper::copyDirectory(FileHelper::normalizePath(__DIR__ . '/xls'), $this->tempdir);

        $this->tempfile = FileHelper::normalizePath(\Yii::getAlias('@runtime') . '/excel-reports/' . $tempname . '.xlsx');
    }

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

    protected function renderBody(): void
    {
        $props = [];
        foreach ($this->columns as ['prop_path' => $prop]) {
            $props[] = $prop;
        }
        $rowSize = '1:' . count($props);
        foreach ($this->query->batch() as $batch) {
            foreach ($batch as $r) {
                $column = 'A';
                $str = "<row r=\"{$this->currentRow}\" spans=\"{$rowSize}\">";
                foreach ($props as $prop) {
                    $offset = $column . $this->currentRow;
                    $column++;
                    $str .= '<c r="' . $offset . '"';
                    $v = array_key_exists($prop, $r) ? (string)$r[$prop] : '';
                    if ($v=='') {
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

        /*foreach ($spreadsheet->getActiveSheet()->getColumnIterator() as $column) {
            $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }*/

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($this->tempfile);
    }

    protected function createExcelFile()
    {
        $zip = new \ZipArchive();
        $zip->open($this->tempfile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $rootPath = realpath($this->tempdir);
        /** @var \SplFileInfo[] $files */
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($rootPath),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($files as $name => $file) {
            // Skip directories (they would be added automatically)
            if (!$file->isDir()) {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);
                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();
        $this->formatDocument();
    }

    protected function send()
    {
        $rs = \Yii::$app->getResponse();
        $name = $this->filename;

        try {
            $rs->getHeaders()
                ->set('Access-Control-Expose-Headers', '*')
                ->set('Content-Disposition', 'attachment; filename="' . $name . '"')
                ->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');

            //base64_encode(file_get_contents($this->tempfile))
            $rs->sendContentAsFile(file_get_contents($this->tempfile), $name, [
                'mimeType' => FileHelper::getMimeTypeByExtension($name),
            ]);

            /*return $rs->sendFile(
                $this->tempfile,  $this->filename,
                ['mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
            );*/
        } finally {
            FileHelper::unlink($this->tempfile);
            FileHelper::removeDirectory($this->tempdir);
        }
    }

    public function export()
    {
        $this->render();
        $this->createExcelFile();

        $this->send();
    }

    /**
     * @throws \yii\web\ServerErrorHttpException
     */
    protected function render()
    {
        $this->sheetFile = FileHelper::normalizePath($this->tempdir . '/xl/worksheets/sheet1.xml');
        $this->stringFile = FileHelper::normalizePath($this->tempdir . '/xl/sharedStrings.xml');

        $this->writeLn($this->sheetFile, $this->cols);
        $this->writeLn($this->sheetFile, '<sheetData>');
        $this->currentRow++;

        $this->renderHead();

        $this->renderBody();

        // финализируем
        $this->writeLn($this->sheetFile, '</sheetData>');

        //$this->writeLn($this->sheetFile, '<dimension ref="A1:' . $this->maxColumn . ($this->currentRow - 1) . '"/>');

        $this->writeLn($this->sheetFile, '<sheetProtection sheet="false" objects="false" scenarios="false" formatCells="false" formatColumns="false" formatRows="false"
                     insertColumns="false" insertRows="false" insertHyperlinks="false" deleteColumns="false" deleteRows="false"
                     selectLockedCells="false" sort="false" autoFilter="false" pivotTables="false" selectUnlockedCells="false"/>');

        $this->writeLn($this->sheetFile, $this->mergeCells ? "<mergeCells>{$this->mergeCells}</mergeCells>" : '<mergeCells/>');

        $this->writeLn($this->sheetFile, '<printOptions gridLines="false" gridLinesSet="true"/>
            <pageMargins left="0.7" right="0.7" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>
            <pageSetup paperSize="1" orientation="default" scale="100" fitToHeight="1" fitToWidth="1"/>
            <headerFooter differentOddEven="false" differentFirst="false" scaleWithDoc="true" alignWithMargins="true">
                <oddHeader></oddHeader>
                <oddFooter></oddFooter>
                <evenHeader></evenHeader>
                <evenFooter></evenFooter>
                <firstHeader></firstHeader>
                <firstFooter></firstFooter>
            </headerFooter>');

        $this->writeLn($this->sheetFile, '</worksheet>');
        $this->writeLn($this->stringFile, '</sst>');
    }

    /**
     * @param string $path
     * @param string $string
     */
    protected function writeLn($path, $string)
    {
        file_put_contents($path, $string, FILE_APPEND | FILE_TEXT | LOCK_EX);
    }

}
