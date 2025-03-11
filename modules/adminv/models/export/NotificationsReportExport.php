<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 19.07.19
 * Time: 14:04
 */

namespace app\modules\adminv\models\export;

use app\modules\admin\models\OrganizationsTree;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use yii\web\RangeNotSatisfiableHttpException;

class NotificationsReportExport
{
    use SendXlsxTrait;

    /**
     * @var Spreadsheet
     */
    protected $spreadsheet;

    /**
     * @var array
     */
    protected $report;

    /**
     * @var int Номер текущей строки
     */
//    protected $currentRowNum = 1;

    const STYLE_HEADER = [
        'font' => [
            'bold' => true,
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
    ];

    const STYLE_TEXT = [
        'font' => [
            'bold' => true,
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_LEFT,
            'vertical' => Alignment::VERTICAL_CENTER,
            'indent' => 1
        ],
    ];

    /**
     * @param array $report
     * @param string $filename
     * @param string $from
     * @param string $to
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws RangeNotSatisfiableHttpException
     */
    public function export($report, $filename = null, $from, $to)
    {
        @ini_set('memory_limit', '512M');

        $this->spreadsheet = new Spreadsheet();
        $this->report = $report;

        $this->renderReport($from, $to, $this->report);

        $this->sendXlsx($this->spreadsheet,$filename);
    }

    /**
     * @param string $from
     * @param string $to
     * @throws Exception
     */
    protected function renderReport($from, $to, $report)
    {
        /*
         * Шапка
         */
        $this->renderHead($from, $to);

        $this->renderBody($report);
    }

    /**
     * вывод строк тела отчета
     */
    protected function renderBody($report)
    {
        $sheet = $this->spreadsheet->getActiveSheet();

        $sheet->getStyleByColumnAndRow(1, 3, 2, 12)
            ->applyFromArray(self::STYLE_TEXT);

        for ($i = 4; $i <= 10; $i++) {
            $sheet
                ->getRowDimension($i)
                ->setRowHeight(18);
        }

        $sheet
            ->mergeCells('A3:A4')
            ->getRowDimension('3');
        $sheet
            ->getColumnDimension('A')
            ->setWidth(35);
        $sheet
            ->getColumnDimension('B')
            ->setWidth(45);
        $sheet
            ->getColumnDimension('C')
            ->setWidth(20);
        $sheet
            ->setCellValue('A3', 'Первое уведомление');
        $sheet
            ->setCellValue('B3', 'о вакцинации');
        $sheet
            ->setCellValue('C3', $report[0]['init_vacc']);
        $sheet
            ->setCellValue('B4', 'об идентификации');
        $sheet
            ->setCellValue('C4', $report[0]['init_ident']);

        $sheet
            ->mergeCells('A5:A6')
            ->getRowDimension('3');
        $sheet
            ->getColumnDimension('A')
            ->setWidth(35);
        $sheet
            ->getColumnDimension('B')
            ->setWidth(45);
        $sheet
            ->getColumnDimension('C')
            ->setWidth(20);
        $sheet
            ->setCellValue('A5', 'Напоминание о вакцинации');
        $sheet
            ->setCellValue('B5', 'бешенство');
        $sheet
            ->setCellValue('C5', $report[0]['rabies']);
        $sheet
            ->setCellValue('B6', 'лептоспироз');
        $sheet
            ->setCellValue('C6', $report[0]['lepto']);

        $sheet
            ->mergeCells('A7:B7')
            ->getColumnDimension('A');
        $sheet
            ->setCellValue('A7', 'Напоминание об идентификации');
        $sheet
            ->setCellValue('C7', $report[0]['ident']);

        $sheet
            ->mergeCells('A8:B8')
            ->getColumnDimension('A');
        $sheet
            ->setCellValue('A8', 'Уведомление о проведении противоэпизоотических мероприятий на местности');
        $sheet
            ->setCellValue('C8', $report[0]['notif_qua']);

        $sheet
            ->mergeCells('A9:B9')
            ->getColumnDimension('A');
        $sheet
            ->setCellValue('A9', 'Уведомление о найденном/отловленном владельческом животном');
        $sheet
            ->setCellValue('C9', $report[0]['found']);

        $sheet
            ->mergeCells('A10:B10')
            ->getColumnDimension('A');
        $sheet
            ->setCellValue('A10', 'Уведомления о готовности результатов исследований');
        $sheet
            ->setCellValue('C10', $report[0]['research']);

        $sheet
            ->mergeCells('A11:A12')
            ->getRowDimension('11');
        $sheet
            ->setCellValue('A11', 'Нарушение');
        $sheet
            ->setCellValue('B11', 'идентификации');
        $sheet
            ->setCellValue('C11', $report[0]['no_ident']);
        $sheet
            ->setCellValue('B12', 'вакцинации');
        $sheet
            ->setCellValue('C12', $report[0]['no_vacc']);
    }

    /**
     * Выводит название отчета с указанием периода и шапку таблицы
     * @param $from
     * @param $to
     * @throws Exception
     */
    protected function renderHead($from, $to)
    {
        $sheet = $this->spreadsheet->getActiveSheet();

        /*
         * Форматирование
         */
        $sheet
            ->mergeCells('A1:D1')
            ->getColumnDimension('A')
            ->setAutoSize(true); // Авто-ширина

        $sheet
            ->getRowDimension('1')
            ->setRowHeight(30);

        $message = 'Сводный отчет по уведомлениям с ' . $from . ' по ' . $to;
        $sheet->setCellValueByColumnAndRow(
            '1',
            1,
            $message
        );

        $sheet
            ->getStyle('A1:F1')
            ->applyFromArray(self::STYLE_HEADER);
    }
}
