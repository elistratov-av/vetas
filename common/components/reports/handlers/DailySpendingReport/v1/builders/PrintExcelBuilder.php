<?php

namespace app\common\components\reports\handlers\DailySpendingReport\v1\builders;

use app\common\components\reports\handlers\DailySpendingReport\v1\PrintDirector;
use app\common\components\reports\interfaces\AbstractPrintBuilder;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * Билдер для формирования файла отчета (документ)
 * Class PrintExcelBuilder
 *
 * @property PrintDirector $director
 * @package app\common\components\reports\handlers\DailySpendingReport\v1\builders
 * @author Aleksandr Roik
 */
class PrintExcelBuilder extends AbstractPrintBuilder
{
    private static $MAP = [
        '01' => 'C',
        '02' => 'D',
        '03' => 'E',
        '04' => 'F',
        '05' => 'G',
        '06' => 'H',
        '07' => 'I',
        '08' => 'J',
        '09' => 'K',
        '10' => 'L',
        '11' => 'M',
        '12' => 'N',
        '13' => 'O',
        '14' => 'P',
        '15' => 'Q',
        '16' => 'R',
        '17' => 'S',
        '18' => 'T',
        '19' => 'U',
        '20' => 'V',
        '21' => 'W',
        '22' => 'X',
        '23' => 'Y',
        '24' => 'Z',
        '25' => 'AA',
        '26' => 'AB',
        '27' => 'AC',
        '28' => 'AD',
        '29' => 'AE',
        '30' => 'AF',
        '31' => 'AG',
    ];

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

        $sheet->getColumnDimension('A')->setAutoSize(true);

        $sheet
            ->setCellValue('Q13', 'за ' . $reportDto->month . ' ' . $reportDto->year)
            ->setCellValue('Q11', 'ежедневного расхода медикаментов и перевязочных материалов в '
                . $reportDto->organization_name . ' ' . $reportDto->specialist_name
            );

        $row = 18;

        foreach ($reportDto->tmc as $tmc) {
            $sheet->insertNewRowBefore($row, 1);
            foreach ($tmc->spent as $spent) {
                $col = $this->dayToCol($spent->day);
                $sheet->setCellValue("$col$row", round($spent->total_spent,4));
            }
            $sheet->setCellValue("A$row", $tmc->name);
            $sheet->setCellValue("B$row", $tmc->measure);
            $sheet->setCellValue("AH$row", round($tmc->total_sum,2));
            $row++;
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
     * @param $day
     * @return mixed
     */
    private function dayToCol($day)
    {
        return self::$MAP[$day];
    }
}
