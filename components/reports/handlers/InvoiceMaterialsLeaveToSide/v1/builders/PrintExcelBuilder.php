<?php

namespace app\common\components\reports\handlers\InvoiceMaterialsLeaveToSide\v1\builders;

use app\common\components\reports\handlers\InvoiceMaterialsLeaveToSide\v1\PrintDirector;
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
 * @package app\common\components\reports\handlers\InvoiceMaterialsLeaveToSide\v1\builders
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
            ->setCellValue('AL3', $reportDto->number)
            ->setCellValue('AC6', $reportDto->acceptor_date->format('d'))
            ->setCellValue('AF6', DateHelper::monthToStr($reportDto->acceptor_date->format('m')))
            ->setCellValue('AO6', $reportDto->acceptor_date->format('y'))
            ->setCellValue('BW5', DateHelper::dateToStr($reportDto->acceptor_date))
            ->setCellValue('P7', $reportDto->sender)
            ->setCellValue('P9', $reportDto->sender_org)
            ->setCellValue('P11', $reportDto->recipient)
            ->setCellValue('P13', $reportDto->recipient_org);

        $sheet
            ->setCellValue('B34', $reportDto->initiator_date->format('d'))
            ->setCellValue('E34', DateHelper::monthToStr($reportDto->initiator_date->format('m')))
            ->setCellValue('N34', $reportDto->initiator_date->format('y'))
            ->setCellValue('B40', $reportDto->initiator_date->format('d'))
            ->setCellValue('E40', DateHelper::monthToStr($reportDto->initiator_date->format('m')))
            ->setCellValue('N40', $reportDto->initiator_date->format('y'))
            ->setCellValue('AA40', $reportDto->acceptor_date->format('d'))
            ->setCellValue('AD40', DateHelper::monthToStr($reportDto->acceptor_date->format('m')))
            ->setCellValue('AM40', $reportDto->acceptor_date->format('y'))
            ->setCellValue('AF32', $reportDto->initiator_specialist->getFullnameInitials())
            ->setCellValue('N37', $reportDto->initiator_specialist->getFullnameInitials())
            ->setCellValue('AM37', $reportDto->acceptor_specialist->getFullnameInitials())
            ->setCellValue('T29', MoneyHelper::sumToStr($reportDto->total_sum));


        $first = true;
        $row = 24;
        foreach ($reportDto->tmc as $tmcDto) {
            $row++;
            if (!$first) {
                $sheet
                    ->insertNewRowBefore($row, 1)
                    ->mergeCells("A$row:L$row")
                    ->mergeCells("M$row:Q$row")
                    ->mergeCells("R$row:V$row")
                    ->mergeCells("W$row:AA$row")
                    ->mergeCells("AB$row:AJ$row")
                    ->mergeCells("AK$row:AO$row")
                    ->mergeCells("AP$row:AT$row")
                    ->mergeCells("AU$row:AY$row")
                    ->mergeCells("AZ$row:BD$row")
                    ->mergeCells("BE$row:BI$row")
                    ->mergeCells("BJ$row:BP$row")
                    ->mergeCells("BQ$row:BV$row")
                    ->mergeCells("BW$row:CD$row");
            }

            $sheet
                ->setCellValue("A$row", $tmcDto->name)
                ->setCellValue("W$row", $tmcDto->measure)
                ->setCellValue("AF$row", $tmcDto->price)
                ->setCellValue("AK$row", $tmcDto->count)
                ->setCellValue("AP$row", $tmcDto->count)
                ->setCellValue("AU$row", $tmcDto->sum_outNDS)
                ->setCellValue("AZ$row", $tmcDto->NDS)
                ->setCellValue("BE$row", $tmcDto->sum);
                //->setCellValue("BQ$row", $tmcDto->initiatorComment)
                //->setCellValue("AW$row", $tmcDto->initiatorComment);

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
        header('Content-Disposition: attachment;filename="'.$this->fileOptions->getName().'"');
        header('Cache-Control: max-age=0');
        header("Pragma: no-cache");

        $writer = IOFactory::createWriter($this->spreadsheet, 'Xls');
        $writer->save('php://output');
        \Yii::$app->getResponse()->isSent = true; //надо, чтобы yii не вздумал отправить еще что...
    }
}
