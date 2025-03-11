<?php

namespace app\modules\adminv\models\excel;

use app\modules\v2\modules\reports\models\VisitJournalReportExport;
use Ramsey\Uuid\Uuid;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

/**
 * Class AbstractReportExport
 * @package app\modules\adminv\models\excel
 */
abstract class AbstractReportExport extends BaseObject
{
    const NO_AREA = 'Округ не найден';
    const NO_DISTRICT = 'Район не найден';
    const NO_ORGANIZATION = 'Организация не найдена';

    /**
     * @var array
     */
    public static $map = [
        // Контроль выездной службы - пока на проде нет данных
        AmbulanceDutyReportExport::class => '01',
        // Отчет по использованию препаратов - пока на проде нет данных
        BalanceTmcReportExport::class => '02',
        // Отчет по загрузке мощностей - пока на проде нет данных
        CapacityReportExport::class => '03',
        // Отчет о нагрузке на ветеринарные учреждения и службы
        ClinicsDutyReportExport::class => '04',
        // Краткая статистика организаций по регистрации и приемам
        CommonReportExport::class => '05',
        // Отчет по работе сотрудников
        EmployeesReportExport::class => '06',
        // Отчет о снятии с учета
        ExpirePetsReportExport::class => '07',
        // Отчет по первично зарегистрированным владельцам/животным
        FirstlyRegReportExport::class => '08',
        // Общий отчет по приемам
        FullVisitsReportExport::class => '09',
        // Детальный отчет по приемам
        FullVisitsVol2ReportExport::class => '10',
        // Статистика mos.ru
        MosRuExport::class => '11',
        // Отчет о выданных регистрационных удостоверениях
        RegPetsReportExport::class => '12',
        // Отчет о пиковых часах загруженности
        RushHoursReportExport::class => '13',
        // Отчет по контролю спроса
        ServicesReportExport::class => '14',
        // Отчет по охвату вакцинацией против бешенства
        VaccinationReportExport::class => '15',
        // Отчет по невакцинированным животным
        UnvaccPetsReportExport::class => '16',
        // Журнал приемов
        VisitJournalReportExport::class => '17',
        // Сводный отчет по уведомлениям
        NotificationReportExport::class => '18',
    ];

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
    public $from;
    /**
     * @var string
     */
    public $to;

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

    /**
     * @inheritDoc
     */
    public function init()
    {
        if (!extension_loaded('zip')) {
            throw new \Exception('Не установлено расширение ZIP');
        }

        $this->filename = strtr($this->filename, ['{from}' => $this->from, '{to}' => $this->to]);

        if ($this->title === null) {
            $this->title = $this->filename;
        } else {
            $this->title = strtr($this->title, ['{from}' => $this->from, '{to}' => $this->to]);
        }

        $this->filename = $this->filename . '.xlsx';

        $tempname = Uuid::uuid4()->toString();
        $this->tempdir = FileHelper::normalizePath(\Yii::getAlias('@runtime') . '/excel-reports/' . $tempname);

        if (!FileHelper::createDirectory($this->tempdir)) {
            throw new ServerErrorHttpException('Не удалось создать временную папку');
        }

        $pattern = ArrayHelper::getValue(self::$map, static::class);
        FileHelper::copyDirectory(FileHelper::normalizePath(__DIR__ . '/patterns/' . $pattern), $this->tempdir);

        $this->tempfile = FileHelper::normalizePath(\Yii::getAlias('@runtime') . '/excel-reports/' . $tempname . '.xlsx');
    }

    /**
     * @return \yii\web\Response
     */
    public function export()
    {
        $this->render();
        $this->createExcelFile();

        return $this->send();
    }

    /**
     * @throws \yii\web\ServerErrorHttpException
     */
    protected function render()
    {
        $this->sheetFile = FileHelper::normalizePath($this->tempdir . '/xl/worksheets/sheet1.xml');
        $this->stringFile = FileHelper::normalizePath($this->tempdir . '/xl/sharedStrings.xml');

        $this->start();

        $this->renderHead();

        $this->renderBody();

        $this->finalize();
    }

    /**
     *
     */
    protected function start()
    {
        $this->writeLn($this->sheetFile, $this->cols);
        $this->writeLn($this->sheetFile, '<sheetData>');
        $this->currentRow++;
    }

    /**
     * @throws \yii\web\ServerErrorHttpException
     */
    abstract protected function renderHead();

    /**
     * @throws \yii\web\ServerErrorHttpException
     */
    abstract protected function renderBody();

    /**
     *
     */
    protected function finalize()
    {
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

    /**
     *
     */
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
    }

    /**
     * @return \yii\web\Response
     */
    protected function send()
    {
        $response = \Yii::$app->response;
        $filepath = $this->tempfile;
        $dirpath = $this->tempdir;

        $response->on(Response::EVENT_AFTER_SEND, function () use ($filepath, $dirpath) {
            FileHelper::unlink($filepath);
            FileHelper::removeDirectory($dirpath);
        });

        return $response->sendFile(
            $filepath,
            $this->filename,
            ['mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    /**
     * Удаляем начальные символы, из-за которых excel воспринимает ячейку как содержащую формулу и выдает ошибку
     * @param string $value
     * @return string
     */
    protected function cleanupCellValue($value)
    {
        $value = trim($value);
        $value = ltrim($value, '=-+^');

        return $value;
    }
}
