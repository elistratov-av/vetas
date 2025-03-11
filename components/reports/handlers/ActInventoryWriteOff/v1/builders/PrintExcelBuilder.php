<?php

namespace app\common\components\reports\handlers\ActInventoryWriteOff\v1\builders;

use app\common\components\reports\handlers\ActInventoryWriteOff\v1\PrintDirector;
use app\common\components\reports\interfaces\AbstractPrintBuilder;
use app\common\helpers\DateHelper;
use app\common\helpers\MoneyHelper;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xls as ReaderXls;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * Билдер для формирования файла отчета (документ)
 * Class PrintExcelBuilder
 *
 * @property PrintDirector $director
 * @package app\common\components\reports\handlers\ActInventoryWriteOff\v1\builders
 * @author Aleksandr Roik
 */
class PrintExcelBuilder extends AbstractPrintBuilder
{
    /**
     * @var Spreadsheet
     */
    public $spreadsheet;

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
        return realpath(__DIR__) . '/../templates/template.xls';
    }

    /**
     * Подготовка отчета, запосление данными
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \yii\db\Exception
     */
    private function prepare()
    {
        $reportDto = $this->director->reportDto;
        $sheet = $this->spreadsheet->getActiveSheet();

        $sheet
            ->setCellValue('A8', 'АКТ О СПИСАНИИ МАТЕРИАЛЬНЫХ ЗАПАСОВ № ' . $reportDto->number)
            ->setCellValue('AC10', $reportDto->acceptorDate->format('d'))
            ->setCellValue('AF10', DateHelper::monthToStr($reportDto->acceptorDate->format('m')))
            ->setCellValue('AO10', $reportDto->acceptorDate->format('y'))
            ->setCellValue('BT10', $reportDto->acceptorDate->format('d.m.Y'))
            ->setCellValue('J11', $reportDto->organizationShortName)
            ->setCellValue('R12', $reportDto->fromOrganizationName)
            ->setCellValue('T13', $reportDto->initiatorSpecialistName)
            ->setCellValue('AV27', $reportDto->sumTotal)
            ->setCellValue('AH29', $reportDto->sumTotal)
            ->setCellValue('AP29', MoneyHelper::sumToStr($reportDto->sumTotal, false))
            ->setCellValue('B44', $reportDto->acceptorDate->format('d'))
            ->setCellValue('E44', DateHelper::monthToStr($reportDto->acceptorDate->format('m')))
            ->setCellValue('N44', $reportDto->acceptorDate->format('y'));

        $first = true;
        $row = 24;
        foreach ($reportDto->tmc as $tmcDto) {
            $row++;
            if (!$first) {
                $sheet
                    ->insertNewRowBefore($row, 1)
                    ->mergeCells("A$row:N$row")
                    ->mergeCells("O$row:S$row")
                    ->mergeCells("T$row:Z$row")
                    ->mergeCells("AA$row:AG$row")
                    ->mergeCells("AH$row:AN$row")
                    ->mergeCells("AO$row:AU$row")
                    ->mergeCells("AV$row:BB$row")
                    ->mergeCells("BC$row:BM$row")
                    ->mergeCells("BN$row:BT$row")
                    ->mergeCells("BU$row:CA$row");
            }

            $sheet
                ->setCellValue("A$row", $tmcDto->name)
                ->setCellValue("T$row", $tmcDto->measure)
                ->setCellValue("AH$row", $tmcDto->count)
                ->setCellValue("AO$row", $tmcDto->price)
                ->setCellValue("AV$row", $tmcDto->sum)
                ->setCellValue("BC$row", $tmcDto->initiatorComment);

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
        header('Content-Transfer-Encoding: binary');
        header('Accept-Ranges: bytes');

        $writer = IOFactory::createWriter($this->spreadsheet, 'Xls');
        $writer->save('php://output');
        \Yii::$app->getResponse()->isSent = true; //надо, чтобы yii не вздумал отправить еще что...
    }
}
