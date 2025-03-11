<?php

namespace app\common\components\reports\handlers\ActVaccineWriteOff\v1\builders;

use app\common\components\reports\handlers\ActVaccineWriteOff\v1\definitions\TemplateFileDefinition;
use app\common\components\reports\handlers\ActVaccineWriteOff\v1\dto\ReportTmcDto;
use app\common\components\reports\handlers\ActVaccineWriteOff\v1\PrintDirector;
use app\common\components\reports\interfaces\AbstractPrintBuilder;
use app\common\components\reports\interfaces\AbstractPrintDirector;
use app\common\components\reports\interfaces\FileOptionsDtoInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xls as ReaderXls;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * Билдер для формирования файла отчета (документ)
 * Class PrintExcelBuilder
 *
 * @property PrintDirector $director
 * @package app\common\components\reports\handlers\ActVaccineWriteOff\v1\builders
 * @author Aleksandr Roik
 */
class PrintExcelBuilder extends AbstractPrintBuilder
{
    /**
     * @var Spreadsheet
     */
    public $spreadsheet;

    /**
     * @var string
     */
    private $template;

    /**
     * @param AbstractPrintDirector $director
     * @param FileOptionsDtoInterface $fileOptions
     * @param null|string $templateFileName Название файла-шаблона
     * @see TemplateFileDefinition
     */
    public function __construct(AbstractPrintDirector $director, FileOptionsDtoInterface $fileOptions, $templateFileName = null)
    {
        parent::__construct($director, $fileOptions);

        $templateFileName = $templateFileName ?? TemplateFileDefinition::TEMPLATE_XLS1;
        $this->template = realpath(__DIR__) . "/../templates/{$templateFileName}.xls";
    }

    /**
     * Запуск построения
     *
     * @return $this
     */
    public function make(): self
    {
        $this->spreadsheet = (new ReaderXls())->load($this->getTemplate());
        $this->prepare();

        return $this;
    }

    /**
     * Возвращает шаблон отчета
     *
     * @return string
     */
    private function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * Подготовка отчета, запосление данными
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \yii\db\Exception
     */
    private function prepare()
    {
        $sheetId = 1;
        foreach ($this->director->reportDto->tmc as $tmc) {
            $this->buildList($tmc, $sheetId);
            $sheetId++;
        }

        //Removing a Worksheet
        $sheetIndex = $this->spreadsheet->getIndex(
            $this->spreadsheet->getSheetByName('list')
        );
        $this->spreadsheet->removeSheetByIndex($sheetIndex);
    }

    /**
     * @param ReportTmcDto $tmc
     * @param $sheetId
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \yii\db\Exception
     */
    private function buildList(ReportTmcDto $tmc, $sheetId)
    {
        $reportDto = $this->director->reportDto;

        $clonedWorksheet = clone $this->spreadsheet->getSheetByName('list');
        $clonedWorksheet->setTitle('list ' . $sheetId);
        $this->spreadsheet->addSheet($clonedWorksheet);
        $sheet = $this->spreadsheet->getSheetByName('list ' . $sheetId);

        $sheet
            ->setCellValue('I3', $reportDto->organizationName)
            ->setCellValue('O4', $reportDto->number)
            ->setCellValue('A6', ($reportDto->acceptorDate ? $reportDto->acceptorDate->format('d.m.Y') : null))
            ->setCellValue('Q11', ($reportDto->acceptorDate ? $reportDto->acceptorDate->format('d.m.Y') : null))
            ->setCellValue('M13', $tmc->dogCount)
            ->setCellValue('W13', $tmc->dogIsSmallCount)
            ->setCellValue('M14', $tmc->catCount)
            ->setCellValue('M15', $tmc->otherAnimalCount)
            ->setCellValue('O16', $tmc->totalCount)
            ->setCellValue('H24', $tmc->tmcName)
            ->setCellValue('J26', $tmc->tmcProduced)
            ->setCellValue('Y26', $tmc->tmcInventoryNumber)
            ->setCellValue('F27', $tmc->tmcExpirationDate ? $tmc->tmcExpirationDate->format('m.Y') . ' г.' : null)
            ->setCellValue('Y26', $tmc->tmcInventoryNumber);

        $countSpecial = count($tmc->specialists);
        $first = true;
        $row1 = 9;
        $row2 = 47;

        if(!$tmc->specialists){
            $row1++;
        }

        foreach ($tmc->specialists as $reportSpecialistDto) {
            if (!$first) {
                $sheet
                    ->insertNewRowBefore($row1)
                    ->mergeCells("A$row1:AB$row1")
                    ->setCellValue('B' . $row2, 'Исполнитель (вет. специалист)');
            }

            $sheet
                ->setCellValue('A' . $row1, 'ветеринарный врач ' . $reportSpecialistDto->organizationName . ' ' . $reportSpecialistDto->specialistName . ($countSpecial > 1 ? ',' : ''))
                ->setCellValue('L' . $row2, $reportSpecialistDto->specialistName);

            $row1 += 1;
            $row2 += 3;
            $countSpecial--;
            $first = false;
        }

        $first = true;
        $row1 += 7;
        foreach ($tmc->petOwners as $petOwner) {
            if (!$first) {
                $sheet->insertNewRowBefore($row1);
                $sheet->mergeCells("A$row1:AC$row1");
            }
            $sheet->setCellValue("A$row1", $petOwner->fullname);
            $row1++;
            $first = false;
        }
    }

    /**
     * Сохраняет файл
     */
    public function toFile()
    {
        $writer = IOFactory::createWriter($this->spreadsheet, 'Xls');
        $writer->save($this->fileOptions->getFullFileName());

        return $this->getResponseFileDto();
    }

    /**
     * Отдает файл в поток
     */
    public function toStream()
    {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $this->fileOptions->getName() . '"');
        header('Cache-Control: max-age=0');
        header("Pragma: no-cache");

        $writer = IOFactory::createWriter($this->spreadsheet, 'Xls');
        $writer->save('php://output');
        \Yii::$app->getResponse()->isSent = true; //надо, чтобы yii не вздумал отправить еще что...
    }
}
