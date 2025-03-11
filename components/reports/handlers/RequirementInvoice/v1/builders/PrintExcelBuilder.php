<?php

namespace app\common\components\reports\handlers\RequirementInvoice\v1\builders;

use app\common\components\reports\dto\ResponseDto;
use app\common\components\reports\dto\ResponseFileDto;
use app\common\components\reports\handlers\RequirementInvoice\v1\PrintDirector;
use app\common\components\reports\interfaces\AbstractPrintBuilder;
use app\common\helpers\DateHelper;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * Билдер для формирования файла отчета (документ)
 * Class PrintExcelBuilder
 *
 * @property PrintDirector $director
 * @package app\common\components\reports\handlers\RequirementInvoice\v1\builders
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
        $this->spreadsheet = (new Xlsx())->load($this->getTemplate());
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
        return realpath(__DIR__) . '/../templates/template.xlsx';
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
            ->setCellValue('AO11', $reportDto->number)
            ->setCellValue('AC14', $reportDto->acceptor_date->format('d'))
            ->setCellValue('AF14', DateHelper::monthToStr($reportDto->acceptor_date->format('m')))
            ->setCellValue('AO14', $reportDto->acceptor_date->format('y'))
            ->setCellValue('BW14', $reportDto->acceptor_date ? $reportDto->acceptor_date->format('d.m.Y') : null)
            ->setCellValue('O15', $reportDto->organization)
            ->setCellValue('O16', $reportDto->sender_org != '' ? $reportDto->sender_org : $reportDto->sender)
            ->setCellValue('O17', $reportDto->recipient_org != '' ? $reportDto->recipient_org : $reportDto->recipient)
            ->setCellValue('S20', $reportDto->acceptor_specialist->getFullnameInitials());

        $first = true;
        $row = 26;

        foreach ($reportDto->tmc as $tmcDto) {
            $row++;
            if (!$first) {
                $sheet
                    ->insertNewRowBefore($row, 1)
                    ->mergeCells("A$row:N$row")
                    ->mergeCells("O$row:S$row")
                    ->mergeCells("T$row:X$row")
                    ->mergeCells("Y$row:AD$row")
                    ->mergeCells("AE$row:AI$row")
                    ->mergeCells("AJ$row:AO$row")
                    ->mergeCells("AP$row:AU$row")
                    ->mergeCells("AV$row:BA$row")
                    ->mergeCells("BB$row:BH$row")
                    ->mergeCells("BI$row:BO$row")
                    ->mergeCells("BP$row:BV$row")
                    ->mergeCells("BW$row:CD$row");
            }

            $sheet
                ->setCellValue("A$row", $tmcDto->name)
                ->setCellValue("Y$row", $tmcDto->measure)
                ->setCellValue("AJ$row", $tmcDto->price)
                ->setCellValue("AP$row", $tmcDto->count)
                ->setCellValue("AV$row", $tmcDto->count)
                ->setCellValue("BB$row", $tmcDto->sum_outNDS);

            $first = false;
        }

        $sheet
            ->setCellValue('BB' . ($row + 1), $reportDto->total_sum_outNDS)
            ->setCellValue('N' . ($row + 4), $reportDto->initiator_specialist->getFullnameInitials())
            ->setCellValue('AM' . ($row + 4), $reportDto->initiator_specialist->getFullnameInitials())
            ->setCellValue('B' . ($row + 7), $reportDto->initiator_date->format('d'))
            ->setCellValue('E' . ($row + 7), DateHelper::monthToStr($reportDto->initiator_date->format('m')))
            ->setCellValue('N' . ($row + 7), $reportDto->initiator_date->format('y'))
            ->setCellValue('AA' . ($row + 7), $reportDto->initiator_date->format('d'))
            ->setCellValue('AD' . ($row + 7), DateHelper::monthToStr($reportDto->initiator_date->format('m')))
            ->setCellValue('AM' . ($row + 7), $reportDto->initiator_date->format('y'))
            ->setCellValue('S' . ($row + 9), $reportDto->acceptor_specialist->getFullnameInitials())
            ->setCellValue('B' . ($row + 12), $reportDto->acceptor_date->format('d'))
            ->setCellValue('E' . ($row + 12), DateHelper::monthToStr($reportDto->acceptor_date->format('m')))
            ->setCellValue('N' . ($row + 12), $reportDto->acceptor_date->format('y'));
    }

    /**
     * Сохраняет файл
     */
    public function toFile(): ResponseFileDto
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
