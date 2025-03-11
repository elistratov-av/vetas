<?php

namespace app\common\components\reports\handlers\MonthlySpendingReport\v1\builders;

use app\common\components\reports\handlers\MonthlySpendingReport\v1\dto\TmcDto;
use app\common\components\reports\handlers\MonthlySpendingReport\v1\PrintDirector;
use app\common\components\reports\interfaces\AbstractPrintBuilder;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * Билдер для формирования файла отчета (документ)
 * Class PrintExcelBuilder
 *
 * @package app\common\components\reports\handlers\MonthlySpendingReport\v1\builders
 * @property PrintDirector $director
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
            ->setCellValue('A4', "с $reportDto->start_date по $reportDto->end_date специалист $reportDto->specialist_name $reportDto->organization_name")
            ->setCellValue('E23', $reportDto->specialist_name);


        $row = 8;
        $row = $this->fillTmcData($row, $reportDto->vaccines_tmc);
        $row += 5;
        $row = $this->fillTmcData($row, $reportDto->alcohol_tmc);
        $row += 5;
        $row = $this->fillTmcData($row, $reportDto->medications_tmc);
        return;
    }

    /**
     * @param int $row
     * @param TmcDto[] $tmc
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function fillTmcData(int $current_row, array $tmc)
    {
        $sheet = $this->spreadsheet->getActiveSheet();
        $row = $current_row;

        $count = 1;
        $insert_count = 0;
        foreach ($tmc as $tmcDto) {
            //Нужно вставлять несколько ячеек прихода/передано
            // Фильтруем, потому что откуда-то прилетает null из-за array_map
            $tmcDto->income = array_values(array_filter($tmcDto->income ?? []));
            $tmcDto->transfer = array_values(array_filter($tmcDto->transfer ?? []));

            $insert_count = count($tmcDto->income) > count($tmcDto->transfer) ?
                count($tmcDto->income)
                : count($tmcDto->transfer);

            $insert_count = $insert_count > 0 ? $insert_count : 1;

            $merge_to_row = $row + $insert_count - 1;

            $sheet
                ->insertNewRowBefore($row, $insert_count)
                ->mergeCells("A$row:A$merge_to_row")
                ->mergeCells("B$row:B$merge_to_row")
                ->mergeCells("C$row:C$merge_to_row")
                ->mergeCells("D$row:D$merge_to_row")
                ->mergeCells("E$row:E$merge_to_row")
                ->mergeCells("F$row:F$merge_to_row")
                ->mergeCells("G$row:G$merge_to_row")
                ->mergeCells("J$row:J$merge_to_row")
                ->mergeCells("K$row:K$merge_to_row")
                ->mergeCells("N$row:N$merge_to_row")
                ->mergeCells("O$row:O$merge_to_row");


            $sheet
                ->setCellValue("A$row", $count)
                ->setCellValue("B$row", $tmcDto->name)
                ->setCellValue("C$row", $tmcDto->measure)
                ->setCellValue("D$row", $tmcDto->inventory_number)
                ->setCellValue("E$row", $tmcDto->expiration_date)
                ->setCellValue("F$row", $tmcDto->price)
                ->setCellValue("G$row", round($tmcDto->remains_start_date, 4))
                ->setCellValue("J$row", round($tmcDto->income_sum, 2))
                ->setCellValue("K$row", round($tmcDto->decrease, 4))
                ->setCellValue("N$row", round($tmcDto->remains_end_date, 4))
                ->setCellValue("O$row", round($tmcDto->decrease_sum, 2));

            for ($i = 0; $i < $insert_count; $i++) {
                $income_row = $row + $i;
                $sheet->setCellValue(
                    "H$income_row",
                    isset($tmcDto->income[$i]->income_count) ? round($tmcDto->income[$i]->income_count, 4) : ''
                );
                $sheet->setCellValue(
                    "I$income_row",
                    $tmcDto->income[$i]->income_specialist ?? ''
                );
                $sheet->setCellValue(
                    "L$income_row",
                    isset($tmcDto->transfer[$i]->transfer_count) ? round($tmcDto->transfer[$i]->transfer_count, 4) : ''
                );
                $sheet->setCellValue(
                    "M$income_row",
                    $tmcDto->transfer[$i]->transfer_specialist ?? ''
                );
            }
            $row += $insert_count;
            $count++;
        }
        return $row;
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
