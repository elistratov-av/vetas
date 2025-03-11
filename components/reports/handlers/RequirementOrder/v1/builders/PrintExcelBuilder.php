<?php

namespace app\common\components\reports\handlers\RequirementOrder\v1\builders;

use app\common\components\reports\handlers\RequirementOrder\v1\PrintDirector;
use app\common\components\reports\interfaces\AbstractPrintBuilder;
use app\common\helpers\DateHelper;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xls as ReaderXls;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

/**
 * Билдер для формирования файла отчета (документ)
 * Class PrintExcelBuilder
 *
 * @property PrintDirector $director
 * @package app\common\components\reports\handlers\RequirementOrder\v1\builders
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
            ->setCellValue('A6', $reportDto->organization_to)
            ->setCellValue('A7', $reportDto->initiator_name)
            ->setCellValue('E8', $reportDto->initiator_date ? $reportDto->initiator_date->format('d.m.Y') : null);

        $first = true;
        $count = 1;
        $row = 11;

        foreach ($reportDto->tmc as $tmcDto) {
            $row++;
            if (!$first) {
                $sheet
                    ->insertNewRowBefore($row, 1)
                    ->mergeCells("B$row:C$row")
                    ->getStyle("A$row:E$row")
                    ->applyFromArray([
                        'borders' => [
                            'outline' => [
                                'borderStyle' => Border::BORDER_HAIR,
                            ],
                        ]
                    ]);
            }

            $sheet
                ->setCellValue("A$row", $count)
                ->setCellValue("B$row", $tmcDto->name)
                ->setCellValue("D$row", $tmcDto->count)
                ->setCellValue("E$row", $tmcDto->measure);

            $count++;
            $first = false;
        }

        $sheet->setCellValue("D" . ($row + 3), $reportDto->initiator_name);
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
