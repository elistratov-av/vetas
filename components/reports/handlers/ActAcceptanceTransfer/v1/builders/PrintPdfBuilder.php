<?php

namespace app\common\components\reports\handlers\ActAcceptanceTransfer\v1\builders;

use app\common\components\reports\handlers\ActAcceptanceTransfer\v1\PrintDirector;
use app\common\components\reports\interfaces\AbstractPrintBuilder;
use app\common\components\reports\interfaces\AbstractPrintDirector;
use app\common\components\reports\interfaces\FileOptionsDtoInterface;
use kartik\mpdf\Pdf;
use Mpdf\HTMLParserMode;
use Mpdf\Output\Destination;
use yii\web\NotFoundHttpException;

/**
 * Билдер для формирования файла отчета (документ)
 * Class PrintPdfBuilder
 *
 * @property PrintDirector $director
 * @package app\common\components\reports\handlers\ActAcceptanceTransfer\v1\builders
 * @author Aleksandr Roik
 */
class PrintPdfBuilder extends AbstractPrintBuilder
{
    /**
     * @var Pdf
     */
    private $pdf;

    /**
     * PrintPdfBuilder constructor.
     *
     * @param AbstractPrintDirector $director
     */
    public function __construct(AbstractPrintDirector $director, FileOptionsDtoInterface $fileOptions)
    {
        parent::__construct($director, $fileOptions);

        $this->pdf = new Pdf([
            // set to use core fonts only
            'mode'         => Pdf::MODE_UTF8,
            // portrait orientation
            'orientation'  => Pdf::ORIENT_PORTRAIT,
            // stream to browser inline
            'destination'  => Pdf::DEST_STRING,
            'marginLeft'   => 10,
            'marginRight'  => 10,
            'marginTop'    => 8,
            'marginBottom' => 8,
        ]);
    }

    /**
     * Запуск построения
     *
     * @return $this
     */
    public function make(): self
    {
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
        return realpath(__DIR__) . '/../templates/template.php';
    }

    /**
     * @return string
     */
    private function getParamsCssInline(): string
    {
        return file_get_contents(realpath(__DIR__) . '/../templates/main.css');
    }

    /**
     * Подготовка отчета, запосление данными
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \yii\db\Exception
     */
    private function prepare()
    {
        $content = $this->getHtmlContent();
        $orient = Pdf::ORIENT_PORTRAIT;

        $pdf = $this->pdf;
        $pdf->cssInline = $this->getParamsCssInline();
        $pdf->getApi()->WriteHTML($pdf->getCss(), HTMLParserMode::HEADER_CSS);
        $pdf->getApi()->_setPageSize(Pdf::FORMAT_A4, $orient);
        $pdf->getApi()->WriteHTML($content);
    }

    /**
     * @return string
     * @throws NotFoundHttpException
     */
    private function getHtmlContent(): string
    {
        $template = $this->getTemplate();
        if (!file_exists($template)) {
            throw new NotFoundHttpException("Шаблон документа не найден");
        }

        return \Yii::$app->view->renderFile(
            $template,
            [
                'dto' => $this->director->reportDto,
            ]
        );
    }

    /**
     * @return string
     * @throws \Mpdf\MpdfException
     */
    private function getPdfContent($destination)
    {
        return $this
            ->pdf
            ->getApi()
            ->Output('', $destination);
    }

    /**
     * Сохраняет файл
     */
    public function toFile()
    {
        if (file_put_contents($this->fileOptions->getFullFileName(), $this->getPdfContent(Destination::STRING_RETURN)) === false) {
            throw new \Exception('Ошибка записи файла');
        }

        return $this->getResponseFileDto();
    }

    /**
     * Отдает файл в поток
     */
    public function toStream()
    {
        $this->getPdfContent(Destination::DOWNLOAD);
    }
}
