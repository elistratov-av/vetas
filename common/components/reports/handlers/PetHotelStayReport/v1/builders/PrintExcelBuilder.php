<?php

namespace app\common\components\reports\handlers\PetHotelStayReport\v1\builders;

use app\common\components\reports\handlers\PetHotelStayReport\v1\dto\TmcDto;
use app\common\components\reports\handlers\PetHotelStayReport\v1\PrintDirector;
use app\common\components\reports\interfaces\AbstractPrintBuilder;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * Билдер для формирования файла отчета (документ)
 * Class PrintExcelBuilder
 *
 * @package app\common\components\reports\handlers\PetHotelStayReport\v1\builders
 * @property PrintDirector $director
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

        $centerBorder = [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                ],
                'left' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                ],
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                ],
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                ],
            ],
        ];

        $sidesBorder = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'left' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                ],
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                ],
            ],
        ];

        $rightBorder = [
            'borders' => [
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                ],
            ],
        ];

        $bottomBorder = [
            'borders' => [
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                ],
            ],
        ];

        $centered = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ]
        ];

        $bold = [
            'font' => [
                'bold' => true,
                ]
            ];

        $other = false;
        if (in_array('Other', $reportDto->animalType)) {
            $other = true;
        }

        $countCells = 1;
        $species = [];
        foreach ($reportDto->animals as $num => $animal) {
            if (!empty($animal->species_name) && empty($species[$animal->species_name])) {
                $species[$animal->species_name] = $animal->species_name;
            }

            $tmFrom = strtotime($animal->date_from);
            $tmTo = strtotime($animal->date_to);
            for ($day = $tmFrom; $day < $tmTo; $day += 60*60*24) {
                $curDate = date('Y-m-d', $day);
                if (empty($animals[$curDate][$animal->species_name])) {
                    $animals[$curDate][$animal->species_name] = 0;
                }
                $animals[$curDate][$animal->species_name] += 1;

                if ($other == true) {
                    $animals[$curDate]['Прочие'] = 0;
                }
            }
        }

        if ($other == true) {
            $species['Прочие'] = 'Прочие';
        }

        $tmFrom = strtotime($reportDto->dateFrom);
        $tmTo = strtotime($reportDto->dateTo);

        for ($day = $tmFrom; $day <= $tmTo; $day += 60*60*24) {
            $curDate = date('Y-m-d', $day);

            foreach($species as $specie) {
                if (!empty($animals[$curDate][$specie])) {
                    $byDays[$curDate][$specie] = $animals[$curDate][$specie];
                }else{
                    $byDays[$curDate][$specie] = 0;
                }
            }
        }

        $byMonths = [];
        for ($day = $tmFrom; $day <= $tmTo; $day += 60*60*24) {
            $curMonth = date('Y-m', $day);
            $curDate = date('Y-m-d', $day);

            foreach($species as $specie) {
                if (empty($byMonths[$curMonth][$specie])) {
                    $byMonths[$curMonth][$specie] = 0;
                }

                $byMonths[$curMonth][$specie] += $byDays[$curDate][$specie];
            }
        }

        $years = [];
        $months = [];
        $quartals = [];
        foreach ($byMonths as $monthNum => $byMonth) {
            $date = explode('-', $monthNum);
            if (empty($years[$date[0]])) {
                $years[$date[0]] = $date[0];
            }
            $quartals[] = $date[0] . '-' . self::getQuartalByMonthNum(intval($date[1]) - 1);
            $months[] = $date[1];
        }

        $sheet->getStyle(self::getSymbolByNum(0) . '1')->applyFromArray($centerBorder);
        $sheet->getStyle(self::getSymbolByNum(0) . '2')->applyFromArray($centerBorder);

        foreach ($months as $num => $month) {
            $sheet->setCellValue(self::getSymbolByNum($num + 1) . '2', self::getMonthByNum(intval($month) - 1));
            $sheet->getStyle(self::getSymbolByNum($num + 1) . '2')->applyFromArray($centerBorder);
            $countCells++;
        }

        $sheet->setCellValue(self::getSymbolByNum($num + 2) . '2', 'Итого');
        $sheet->getStyle(self::getSymbolByNum($num + 2) . '2')->applyFromArray($centerBorder);

        $totalSpecies = [];
        $totalByMonths = [];
        foreach (array_values($byMonths) as $byMonthNum => $byMonth) {
            $totalByMonths[$byMonthNum] = 0;
            foreach (array_values($byMonth) as $itemNum => $item) {
                $sheet->setCellValue(self::getSymbolByNum($byMonthNum + 1) . ($itemNum + 4), $item);
                $sheet->getStyle(self::getSymbolByNum($byMonthNum + 1) . ($itemNum + 4))->applyFromArray($centered);
                $totalByMonths[$byMonthNum] += $item * $reportDto->priceForDay;
                if (empty($totalSpecies[$itemNum])) {
                    $totalSpecies[$itemNum] = 0;
                }
                $totalSpecies[$itemNum] += $item;
            }
        }

        $sheet->setCellValue(self::getSymbolByNum(0) . '3', 'Выручка');
        $sheet->getStyle(self::getSymbolByNum(0) . 3)->applyFromArray($sidesBorder);

        $total = 0;
        foreach ($totalByMonths as $num => $totalByMonth) {
            $sheet->setCellValue(self::getSymbolByNum($num + 1) . '3', $totalByMonth . ' Р');
            $sheet->getStyle(self::getSymbolByNum($num + 1) . '3')->applyFromArray($centered);
            $total += $totalByMonth;
        }

        $sheet->setCellValue(self::getSymbolByNum($num + 2) . '3', $total . ' Р');
        $sheet->getStyle(self::getSymbolByNum($num + 2) . '3')->applyFromArray($rightBorder);
        $sheet->getStyle(self::getSymbolByNum($num + 2) . '3')->applyFromArray($centered);

        foreach($totalSpecies as $totalSpecieNum => $totalSpecie) {
            $sheet->setCellValue(self::getSymbolByNum($num + 2) . ($totalSpecieNum + 4), $totalSpecie);
            $sheet->getStyle(self::getSymbolByNum($num + 2) . ($totalSpecieNum + 4))->applyFromArray($rightBorder);
            $sheet->getStyle(self::getSymbolByNum($num + 2) . ($totalSpecieNum + 4))->applyFromArray($centered);
        }
        $sheet->getStyle(self::getSymbolByNum($num + 2) . ($totalSpecieNum + 4))->applyFromArray($bottomBorder);

        foreach (array_values($species) as $num => $specie) {
            $sheet->setCellValue(self::getSymbolByNum(0) . ($num + 4), ucfirst($specie));
            $sheet->getStyle(self::getSymbolByNum(0) . ($num + 4))->applyFromArray($sidesBorder);
        }

        $sheet->getStyle(self::getSymbolByNum(0) . ($num + 5))->applyFromArray($centerBorder);

        $oldQuartalNum = 0;
        $oldQuartal = $quartals[0];
        foreach ($quartals as $quartalNum => $curQuartal) {
            if ($oldQuartal != $curQuartal || ($quartalNum == count($quartals) - 1)) {
                if (($quartalNum == count($quartals) - 1)) {
                    $quartalNum++;
                }

                $sheet->mergeCells(self::getSymbolByNum($oldQuartalNum + 1) . ($num + 5) . ':' . self::getSymbolByNum($quartalNum) . ($num + 5));
                $sheet->setCellValue(self::getSymbolByNum($oldQuartalNum + 1) . ($num + 5) , (explode('-', $oldQuartal)[1]) . ' Квартал');
                $sheet->getStyle(self::getSymbolByNum($oldQuartalNum + 1) . ($num + 5) . ':' . self::getSymbolByNum($quartalNum) . ($num + 5))->applyFromArray($centerBorder);

                for ($i = ($num + 6); $i > 1; $i--) {
                    $sheet->getStyle(self::getSymbolByNum($quartalNum) . $i)->applyFromArray($rightBorder);
                }

                foreach (array_values($species) as $specieNum => $specie) {
                    $sheet->mergeCells(self::getSymbolByNum($oldQuartalNum + 1) . ($specieNum + $num + 6) . ':' . self::getSymbolByNum($quartalNum) . ($specieNum + $num + 6));
                    $sheet->getStyle(self::getSymbolByNum($oldQuartalNum + 1) . ($specieNum + $num + 6) . ':' . self::getSymbolByNum($quartalNum) . ($specieNum + $num + 6))->applyFromArray($rightBorder);
                }

                $sheet->mergeCells(self::getSymbolByNum($oldQuartalNum + 1) . ($specieNum + $num + 7) . ':' . self::getSymbolByNum($quartalNum) . ($specieNum + $num + 7));
                $sheet->getStyle(self::getSymbolByNum($oldQuartalNum + 1) . ($specieNum + $num + 7) . ':' . self::getSymbolByNum($quartalNum) . ($specieNum + $num + 7))->applyFromArray($rightBorder);
                $sheet->getStyle(self::getSymbolByNum($oldQuartalNum + 1) . ($specieNum + $num + 7) . ':' . self::getSymbolByNum($quartalNum) . ($specieNum + $num + 7))->applyFromArray($bottomBorder);

                $quartalAll = 0;
                $quartalTotals = self::calcTotalByQuartal($oldQuartal, $byMonths);
                foreach (array_values($quartalTotals) as $quartalTotalNum => $quartalTotal) {
                    $quartalAll += $quartalTotal;
                    $sheet->setCellValue(self::getSymbolByNum($oldQuartalNum + 1) . ($quartalTotalNum + $num + 7) , $quartalTotal);
                    $sheet->getStyle(self::getSymbolByNum($oldQuartalNum + 1) . ($quartalTotalNum + $num + 7))->applyFromArray($centered);
                }

                $sheet->setCellValue(self::getSymbolByNum($oldQuartalNum + 1) . ($num + 6) , ($quartalAll * $reportDto->priceForDay) . ' Р');
                $sheet->getStyle(self::getSymbolByNum($oldQuartalNum + 1) . ($num + 6))->applyFromArray($centered);

                $oldQuartal = $curQuartal;
                $oldQuartalNum = $quartalNum;
            }
        }

        $sheet->setCellValue(self::getSymbolByNum(0) . ($num + 6), 'Выручка');
        $sheet->getStyle(self::getSymbolByNum(0) . ($num + 6))->applyFromArray($sidesBorder);

        foreach (array_values($species) as $specieNum => $specie) {
            $sheet->setCellValue(self::getSymbolByNum(0) . ($num + $specieNum + 7), ucfirst($specie));
            $sheet->getStyle(self::getSymbolByNum(0) . ($num + $specieNum + 7))->applyFromArray($sidesBorder);
        }

        $sheet->getStyle(self::getSymbolByNum(0) . ($num + $specieNum + 7))->applyFromArray($bottomBorder);

        $sheet->mergeCells('A1:' . self::getSymbolByNum($countCells) . '1');
        $sheet->getStyle('A1:' . self::getSymbolByNum($countCells) . '1')->applyFromArray($centerBorder);
        $sheet->setCellValue('A1', 'Зоогостиница ' . implode('-', $years));

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
        header('Access-Control-Allow-Origin: *');
        header('Cache-Control: max-age=0');
        header("Pragma: no-cache");

        $writer = IOFactory::createWriter($this->spreadsheet, 'Xls');
        $writer->save('php://output');
        \Yii::$app->getResponse()->isSent = true; //надо, чтобы yii не вздумал отправить еще что...
    }

    private static  function calcTotalByQuartal($quartal, $byMonths)
    {
        $quartalDate = explode('-', $quartal);
        foreach($byMonths as $byMonthNum => $byMonth) {
            $curDate = explode('-', $byMonthNum);
            if ($quartalDate[0] == $curDate[0]) {
                $curQuartal = self::getQuartalByMonthNum(intval($curDate[1]) - 1);
                if ($quartalDate[1] == $curQuartal) {
                    foreach ($byMonth as $specieName => $specie) {
                        if (empty($total[$specieName])) {
                            $total[$specieName] = 0;
                        }
                        $total[$specieName] += $specie;
                    }
                }
            }
        }

        return $total;
    }

    private static function getSymbolByNum($num)
    {
        $data = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD'];

        return $data[$num];
    }

    private static function getMonthByNum($num)
    {
        $data = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];

        return $data[$num];
    }

    private static function getQuartalByMonthNum($num)
    {
        $data = [1,1,1,2,2,2,3,3,3,4,4,4];

        return $data[$num];
    }
}
