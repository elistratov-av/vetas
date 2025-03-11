<?php

namespace app\common\components\reports\handlers\OrganizationReport\v1\builders;

use app\common\components\reports\handlers\OrganizationReport\v1\dto\MovementDto;
use app\common\components\reports\handlers\OrganizationReport\v1\dto\TmcDto;
use app\common\components\reports\handlers\OrganizationReport\v1\PrintDirector;
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
 * @package app\common\components\reports\handlers\OrganizationReport\v1\builders
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
            ->setCellValue('A14', 'и вспомогательных материалов за '
                . $reportDto->month . ' ' . $reportDto->year . ' г.'
            )
        ->setCellValue('Q10', 'Начальник ' . $reportDto->organization_name);

        $row = 18;
        $count = 1;

        foreach ($reportDto->specialist_tmc as $key => $tmc) {
            $sheet->insertNewRowBefore($row, 1);
            $sheet
                ->setCellValue("A$row", $count)
                ->setCellValue("B$row", array_values($tmc)[0]->name)
                ->setCellValue("C$row", array_values($tmc)[0]->measure)
                ->setCellValue("D$row", array_values($tmc)[0]->expiration_date);
            $row++;
            $first_row = $row; // Для вычисления суммы
            foreach ($tmc as $specialist) {
                $sheet->insertNewRowBefore($row, 1);


                $sheet->setCellValue("B$row", $specialist->specialist_name);

                $this->fillMovements($row, 'E', $specialist->remains_start_date);
                $this->fillMovements($row, 'H', $specialist->income);
                $this->fillMovements($row, 'K', $specialist->decrease);
                $this->fillMovements($row, 'N', $specialist->transfer);
                $this->fillMovements($row, 'Q', $specialist->remains_end_date);

                $row++;
            }
            $sheet->insertNewRowBefore($row, 1);
            $sum_row = $row - 1;
            $sheet
                ->setCellValue("B$row", "Итого по МОЛ")
                ->setCellValue("E$row", "=SUM(E$first_row:E$sum_row)")
                ->setCellValue("F$row", "=SUM(F$first_row:F$sum_row)")
                ->setCellValue("G$row", "=SUM(G$first_row:G$sum_row)")
                ->setCellValue("H$row", "=SUM(H$first_row:H$sum_row)")
                ->setCellValue("I$row", "=SUM(I$first_row:I$sum_row)")
                ->setCellValue("J$row", "=SUM(J$first_row:J$sum_row)")
                ->setCellValue("K$row", "=SUM(K$first_row:K$sum_row)")
                ->setCellValue("L$row", "=SUM(L$first_row:L$sum_row)")
                ->setCellValue("M$row", "=SUM(M$first_row:M$sum_row)")
                ->setCellValue("N$row", "=SUM(N$first_row:N$sum_row)")
                ->setCellValue("O$row", "=SUM(O$first_row:O$sum_row)")
                ->setCellValue("P$row", "=SUM(P$first_row:P$sum_row)")
                ->setCellValue("Q$row", "=SUM(Q$first_row:Q$sum_row)")
                ->setCellValue("R$row", "=SUM(R$first_row:R$sum_row)")
                ->setCellValue("S$row", "=SUM(S$first_row:S$sum_row)");

            $row++;
            $sheet->insertNewRowBefore($row, 1);
            $sheet->setCellValue("B$row", "Склад");
            if (!array_key_exists($key, $reportDto->technic_tmc)){
                $row ++;
                $count++;
                continue;
            }
            $this->fillMovements($row, 'E', $reportDto->technic_tmc[$key]->remains_start_date);
            $this->fillMovements($row, 'H', $reportDto->technic_tmc[$key]->income);
            $this->fillMovements($row, 'K', $reportDto->technic_tmc[$key]->decrease);
            $this->fillMovements($row, 'N', $reportDto->technic_tmc[$key]->transfer);
            $this->fillMovements($row, 'Q', $reportDto->technic_tmc[$key]->remains_end_date);
            $row ++;
            $count++;
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

    /**
     * @param $specialist_dto TmcDto
     */
    private function getInsertCount($specialist_dto)
    {
        $max = max(
            count($specialist_dto->remains_end_date),
            count($specialist_dto->remains_start_date),
            count($specialist_dto->income),
            count($specialist_dto->transfer),
            count($specialist_dto->decrease)
        );
        return $max == 0 ? 1 : $max;
    }

    /**
     * @param int $current_row
     * @param string $start_col
     * @param MovementDto $movement
     */
    private function fillMovements(int $current_row, string $start_col, MovementDto $movement)
    {
        $sheet = $this->spreadsheet->getActiveSheet();

        $count_col = $start_col;
        $price_col = ++$start_col;
        $sum_col   = ++$start_col;
        //Почему-то возникает ошибка "попытки обратиться к свойству не объекта"
        //Локально повторить кейс не смог, но дто имплементирует array access, так что заменил
        if ($movement['count'] == 0) {
            return;
        }
        $sheet
            ->setCellValue("$count_col$current_row", round($movement['count'], 4) ?? '')
            ->setCellValue("$price_col$current_row", round($movement['price'], 2) ?? '')
            ->setCellValue("$sum_col$current_row", round($movement['sum'], 2) ?? '');
    }

}
