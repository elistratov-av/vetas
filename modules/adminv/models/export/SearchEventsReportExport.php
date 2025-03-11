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

class SearchEventsReportExport
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

        $sheet->getStyleByColumnAndRow(1, 3, 1, 5)
            ->applyFromArray(self::STYLE_TEXT);

        for ($i = 4; $i <= 10; $i++) {
            $sheet
                ->getRowDimension($i)
                ->setRowHeight(18);
        }

        $sheet
            ->setCellValue('A3', 'Уведомления о публикации нового объявления');
        $sheet
            ->setCellValue('B3', $report['8021.1']['total'] ?? 0);
        $sheet
            ->setCellValue('A4', 'Уведомления о подписке на рассылку новых объявлений');
        $sheet
            ->setCellValue('B4', $report['8021.2']['total'] ?? 0);

        $sheet
            ->setCellValue('A5', 'Уведомления об архивации размещенных объявлений');
        $sheet
            ->setCellValue('B5', $report['1075.3']['total'] ?? 0);
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
            ->mergeCells('A1:B1')
            ->getColumnDimension('A')
            ->setAutoSize(true); // Авто-ширина

        $sheet
            ->getRowDimension('1')
            ->setRowHeight(30);

        $message = 'Статистика отправки уведомлений по поиску с ' . $from . ' по ' . $to;
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
