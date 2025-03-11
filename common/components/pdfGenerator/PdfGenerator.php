<?php

namespace app\common\components\pdfGenerator;

use app\common\components\media\ResourceFileRepository;
use app\common\components\visitServiceReport\helpers\ServiceReportsDataHelper;
use app\common\components\visitServiceReport\ServiceReportActions;
use app\common\helpers\DateHelper;
use DateTime;
use kartik\mpdf\Pdf;
use Mpdf\HTMLParserMode;
use Mpdf\MpdfException;
use Mpdf\Output\Destination;
use Throwable;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;
use yii\web\NotFoundHttpException;
use app\common\components\visitServiceReport\helpers\ServiceReportActionsTrait;
use yii\helpers\ArrayHelper;
use yii\web\ServerErrorHttpException;

class PdfGenerator extends ResourceFileRepository
{

    /** Шаблон согласия на обработку персональных данных */
    public const PERS_DATA_TEMPLATE = 'pers_data_agreement';
    /** Шаблон согласия на хирургическое вмешательство */
    public const SURGERY_TEMPLATE = 'surgery_agreement';
    /** Шаблон приема */
    public const VISIT_DESCRIPTIONS_TEMPLATE = 'visit_descriptions_2';
    /** Шаблон заголовка приема */
    public const HEADER_VISIT_DESCRIPTIONS_TEMPLATE = 'header_visit_description';

    public const TOMOGRAPHY_REPORT_TEMPLATE = 'tomography_report';
    /** Формат файла печати */
    public const FILE_FORMAT = 'pdf';
    /** Формат печати страницы А5 */
    public const FORMAT_A5 = 'A5';
    /** Формат печати страницы A4 */
    public const FORMAT_A4 = 'A4';
    /** Гельминто-копрологическое исследование */
    public const VSD_REPORT = 4;
    use ServiceReportActionsTrait;
    /**
     * @var ServiceReportsDataHelper
     */
    private $handler;

    /**
     * @return ServiceReportsDataHelper
     */
    public function getHandler(): ServiceReportsDataHelper
    {
        return $this->handler;
    }

    /** @var string */
    private $templateDir;

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();

        $this->path = Yii::$app->params['resources_media_dir'];
        $this->templateDir = Yii::getAlias('@app') . '/common/components/pdfGenerator/template/';
    }

    /**
     * Генерирует PDF документ
     *
     * @param array $contents html контент [content => '', format => 'A4', orientation => 'P']
     * @param array $params   другие параметры
     * @param array $options
     *
     * @return mixed
     * @throws InvalidConfigException
     * @throws MpdfException
     */
    public function generate(array $contents, array $params = [], array $options = [])
    {
        // setup kartik\mpdf\Pdf component
        $pdf = new Pdf([
            // set to use core fonts only
            'mode' => Pdf::MODE_UTF8,
            // portrait orientation
            'orientation' => Pdf::ORIENT_PORTRAIT,
            // stream to browser inline
            'destination' => Pdf::DEST_STRING,
            'marginLeft' => 10,
            'marginRight' => 10,
            'marginTop' => 8,
            'marginBottom' => 8,
            'options' => $options,
        ]);

        if (array_key_exists('cssInline', $params)) {
            $pdf->cssInline = $params['cssInline']; // без этой обертки не подтянутся шрифты
            $pdf->getApi()->WriteHTML($pdf->getCss(), HTMLParserMode::HEADER_CSS);
        }
        if (array_key_exists('htmlHeader', $params)) {
            $pdf->getApi()->setHtmlHeader($params['htmlHeader']);
        }

        foreach ($contents as $content) {
            $orient = array_key_exists('orientation', $content) ? $content['orientation'] : Pdf::ORIENT_PORTRAIT;
            $pdf->getApi()->_setPageSize(
                array_key_exists('format', $content) ? $content['format'] : Pdf::FORMAT_A4,
                $orient
            );
            $pdf->getApi()->WriteHTML($content['content']);
        }

        // return the pdf output as per the destination setting
        return $pdf->getApi()->Output('', Destination::STRING_RETURN);
    }

    /**
     * @param int|string $code шаблон документа
     * @param array      $data значения
     * @param bool       $cssInline
     *
     * @return array данные pdf документа [$dir, $filename, $ext]
     * @throws Exception
     * @throws NotFoundHttpException
     * @throws MpdfException
     */
    public function createDocument($code, array $data, bool $cssInline = true): array
    {
        $paramsCssInline = ($cssInline === true) ? file_get_contents($this->templateDir . '/css/main.css') : '';
        $html_contents = [];

        if (!is_array($code)) {
            $code = [$code];
        }

        foreach ($code as $i => $templateId) {
            $paramsCssInline .= $this->getCss($templateId);
            $html_contents[] = $i === 0 ?
                [
                    'content' => $this->getHtmlContent($templateId, $data),
                ]
                : [
                    'content' => '<pagebreak resetpagenum=1/>' . $this->getHtmlContent($templateId, $data),
                ];
        }

        return self::savePDF($this->generate($html_contents, ['cssInline' => $paramsCssInline]));
    }
    /**
     * @param int|string $code шаблон документа
     * @param array      $data значения
     * @param bool       $cssInline
     *
     * @return array данные pdf документа [$dir, $filename, $ext]
     * @throws Exception
     * @throws NotFoundHttpException
     * @throws MpdfException
     */
    public function createDocumentPrintCard($code, array $data, bool $cssInline = true): array
    {
        $paramsCssInline = ($cssInline === true) ? file_get_contents($this->templateDir . '/css/main.css') : '';
        $html_contents = [];

        if (!is_array($code)) {
            $code = [$code];
        }

        foreach ($code as $i => $templateId) {
            $paramsCssInline .= $this->getCss($templateId);
            $html_contents[] = $i === 0 ?
                [
                    'content' => $this->getHtmlContent($templateId, $data),
                ]
                : [
                    'content' => '<pagebreak resetpagenum=1/>' . $this->getHtmlContent($templateId, $data),
                ];
        }
        // добавляем список отчетов иследований для которых имеются данные
        if ($reports = PdfVisitGeneratorHelper::getReportsForPrint($data['visit']->id)) {
            $visitParams = PdfVisitGeneratorHelper::getVisitParamValues($data['visit']->id);
            $paramsCssInline .= $this->getCss('main');

            foreach ($reports as $report) {
                $visitParamsPet = PdfVisitGeneratorHelper::getServicesParamForReport($data['visit']->id, $report);
                // ВСД с исследованием печатается столько, сколько раз была оказана
                if ($report === self::VSD_REPORT) {
                    $vsdReportsData = PdfVisitGeneratorHelper::getServiceParamsForVSDReport($data['visit']->id);
                    foreach ($vsdReportsData as $vsdReportData) {
                        $html_contents[] = [
                            // 'format' => $format,
                            'content' => '<sethtmlpageheader value="off"/><pagebreak resetpagenum=1/>' .
                                $this->getHtmlContent($report, array_merge(
                                    $visitParams,
                                    $vsdReportData
                                )),
                        ];
                    }
                } else {
                    $html_contents[] = [
                        // 'format' => $format,
                        'content' => '<sethtmlpageheader value="off"/><pagebreak resetpagenum=1/>' .
                            $this->getHtmlContent($report, array_merge(
                                $visitParams,
                                $visitParamsPet
                            )),
                    ];
                    $paramsCssInline .= $this->getCss($report);
                }
            }
        }
        return self::savePDF($this->generate($html_contents, ['cssInline' => $paramsCssInline]));
    }

    /**
     * @param int|string $code шаблон документа
     * @param array      $dataList список значений
     * @param bool       $cssInline
     *
     * @return array данные pdf документа [$dir, $filename, $ext]
     * @throws Exception
     * @throws NotFoundHttpException
     * @throws MpdfException
     */
    public function createDocumentList($code, array $dataList, bool $cssInline = true): array
    {
        $paramsCssInline = ($cssInline === true) ? file_get_contents($this->templateDir . '/css/main.css') : '';
        $html_contents = [];

        if (!is_array($code)) {
            $code = [$code];
        }

        $page = 1;
        $pages = count($code) + count($dataList) - 1;

        foreach ($code as $i => $templateId) {
            foreach ($dataList as $data) {
                $data['numbering'] = [
                    'page' => $page++,
                    'pages' =>  $pages,
                ];
                $paramsCssInline .= $this->getCss($templateId);
                $html_contents[] = $i === 0 ?
                    [
                        'content' => $this->getHtmlContent($templateId, $data),
                    ]
                    : [
                        'content' => '<pagebreak resetpagenum=1/>' . $this->getHtmlContent($templateId, $data),
                    ];
            }
        }

        return self::savePDF($this->generate($html_contents, ['cssInline' => $paramsCssInline]));
    }

    public function createTomographyReport(array $data, string $format = PdfGenerator::FORMAT_A4): array
    {
        $paramsCssInline = $this->getCss(self::TOMOGRAPHY_REPORT_TEMPLATE);

        $html_contents = [];
        foreach ($data['pet_ids'] as $petId) {
            $data1 = $data;
            $data1['pet_id'] = $petId;
            $html_contents[] = [
                'content' => $this->getHtmlContent(self::TOMOGRAPHY_REPORT_TEMPLATE, $data1),
                'format' => $format
            ];
        }

        return self::savePDF(
            $this->generate(
                $html_contents,
                [
                    'cssInline' => $paramsCssInline,
                    'htmlHeader' => $this->getHtmlContent(self::HEADER_VISIT_DESCRIPTIONS_TEMPLATE)
                ],
                ['setAutoTopMargin' => 'stretch']
            )
        );
    }

    /**
     * @param array  $data
     * @param string $format
     *
     * @return array
     * @throws Throwable
     */
    public function createVisitDescription(array $data, string $format = PdfGenerator::FORMAT_A4): array
    {
        $paramsCssInline = $this->getCss(self::VISIT_DESCRIPTIONS_TEMPLATE);
        $html_contents[] = [
            'content' => $this->getHtmlContent(self::VISIT_DESCRIPTIONS_TEMPLATE, $data),
            'format' => $format
        ];

        // добавляем список отчетов иследований для которых имеются данные
        if ($reports = PdfVisitGeneratorHelper::getReportsForPrint($data['visit']->id)) {
            $visitParams = PdfVisitGeneratorHelper::getVisitParamValues($data['visit']->id);
            $paramsCssInline .= $this->getCss('main');

            $idServiceIdUsed = [];
            foreach ($reports as $report) {
                // ВСД с исследованием печатается столько, сколько раз была оказана
                if ($report === self::VSD_REPORT) {
                    $vsdReportsData = PdfVisitGeneratorHelper::getServiceParamsForVSDReport($data['visit']->id);
                    foreach ($vsdReportsData as $vsdReportData) {
                        $html_contents[] = [
                            'format' => $format,
                            'content' => '<sethtmlpageheader value="off"/><pagebreak resetpagenum=1/>' .
                                $this->getHtmlContent($report, array_merge(
                                    $visitParams,
                                    $vsdReportData
                                )),
                        ];
                    }
                } else {

                    $id_services = PdfVisitGeneratorHelper::getSerialServiceId($data['visit']->id, $report);
                    $pets = array_values(ArrayHelper::index($id_services, null, 'id_pet'));
                    $allData = [
                        'id_report' => $report,
                        'params' => []
                    ];
                    foreach ($pets as $idx => $pet) {
                        $servicesGroups = array_values(ArrayHelper::index($pet, null, 'id_service_type'));
                        foreach ($servicesGroups as $servicesGroup) {
                            foreach ($servicesGroup as $service) {
                                $idForOutput = ['id' => $service['id']];
                                if (in_array($service['id'], $idServiceIdUsed)) {
                                    continue;
                                }
                                $idServiceIdUsed[] = $service['id'];
                                $reportParamsValues = $this->findParamsValues($idForOutput, 'files');
                                $datas = $this->handler->prepareOutput($reportParamsValues, true);
                                if (!$allData['params'][$idx]) $allData['params'][$idx] = $datas['params'][0];
                                else {
                                    foreach ($datas['params'][0] as $key => $param) {
                                        if (!isset($allData['params'][$idx][$key])) $allData['params'][$idx][$key] = $param;
                                    }
                                }
                                $allData['params'][0]['P0_Petchpidentificationcode'] = $pet[0]['chips'];
                            }
                        }
                    }
                    foreach ($allData['params'] as $datates) {
                        $html_contents[] = [
                            'format' => $format,
                            'content' => '<sethtmlpageheader value="off"/><pagebreak resetpagenum=1/>' .
                                $this->getHtmlContent($allData['id_report'], $datates)
                        ];
                    }
                    $paramsCssInline .= $this->getCss($allData['id_report']);
                }
            }
        }
        if (!isset($data['organizations'])) $data['organizations'] = [];
        return self::savePDF(
            $this->generate(
                $html_contents,
                [
                    'cssInline' => $paramsCssInline,
                    'htmlHeader' => $this->getHtmlContent(self::HEADER_VISIT_DESCRIPTIONS_TEMPLATE, ['organizations' => $data['organizations']])
                ],
                ['setAutoTopMargin' => 'stretch']
            )
        );
    }
    /**
     * @param array  $data
     * @param string $format
     *
     * @return array
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function createPersAgreementFile(array $data, string $format): array
    {
        $paramsCssInline = $this->getCss(self::PERS_DATA_TEMPLATE);
        $html_contents[] = [
            'content' => $this->getHtmlContent(self::PERS_DATA_TEMPLATE, $data),
            'format' => $format
        ];

        return self::savePDF(
            $this->generate(
                $html_contents,
                [
                    'cssInline' => $paramsCssInline,
                ],
                ['setAutoTopMargin' => 'stretch']
            )
        );
    }

    /**
     * @param array  $data
     * @param string $format
     *
     * @return array
     * @throws Exception
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function createSurgAgreementFile(array $data, string $format): array
    {
        $paramsCssInline = $this->getCss(self::SURGERY_TEMPLATE);
        $html_contents[] = [
            'content' => $this->getHtmlContent(self::SURGERY_TEMPLATE, $data),
            'format' => $format
        ];

        return self::savePDF(
            $this->generate(
                $html_contents,
                [
                    'cssInline' => $paramsCssInline,
                ],
                ['setAutoTopMargin' => 'stretch']
            )
        );
    }

    /**
     * @param string $date
     * @param string $format
     *
     * @return array
     */
    public static function dateFromFormat(string $date, string $format): array
    {
        $date = DateTime::createFromFormat($format, $date);
        if ($date === false) {
            return [
                'd' => '',
                'm' => '',
                'M' => '',
                'Y' => '',
            ];
        }
        $months = [1 => 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];

        return [
            'd' => $date->format('d'),
            'm' => $date->format('m'),
            'M' => $months[$date->format('n')],
            'Y' => $date->format('Y'),
        ];
    }

    /**
     * @param string $key
     * @param array  $data
     *
     * @return string|null
     */
    public static function getValue(string $key, array $data, $default = '&nbsp;'): ?string
    {
        if (array_key_exists($key, $data)) {
            if ($data[$key] === null || $data[$key] === '') {
                return $default;
            }
            return is_array($data[$key]) ? implode(', ', $data[$key]) : $data[$key];
        }

        return $default;
    }

    /**
     * @param array  $data
     * @param string $emptyValue
     *
     * @return string
     */
    public static function petAge(array $data = [], string $emptyValue = '&nbsp;'): string
    {
        if (empty($data['P3_Visitstartdate']) || empty($data['P10_Petbirthday'])) {
            return $emptyValue;
        }

        $date = DateHelper::ageAtDate(
            $data['P10_Petbirthday'],
            $data['P3_Visitstartdate'],
            'd.m.Y',
            'd.m.Y'
        );
        return $date ?: $emptyValue;
    }

    /**
     * @param array  $data
     * @param string $emptyValue
     *
     * @return string
     */
    public static function petSex(array $data = [], string $emptyValue = '&nbsp;'): string
    {
        $petSex = self::getValue('P8_Petsex', $data);
        if ($petSex === null || ($petSex !== 'm' && $petSex !== 'f')) {
            return $emptyValue;
        }

        //return $petSex === 'm' ? 'Мужской' : 'Женский';
        return $petSex;
    }

    /**
     * @param string $templateId
     * @param array  $data
     *
     * @return string
     * @throws NotFoundHttpException
     */
    private function getHtmlContent(string $templateId, array $data = []): string
    {
        $template = $this->templateDir . 'views/' . $templateId . '.php';
        if (!file_exists($template)) {
            throw new NotFoundHttpException("Документ {$template} не найден");
        }

        return Yii::$app->view->renderFile(
            $template,
            [
                'data' => $data,
                'template_dir' => $this->templateDir,
            ]
        );
    }

    /**
     * @param string $templateId
     *
     * @return string
     */
    private function getCss(string $templateId): string
    {
        $css = $this->templateDir . '/css/' . $templateId . '.css';
        return file_exists($css) ? file_get_contents($css) : '';
    }

    /**
     * @param string $pdfContent
     *
     * @return array
     * @throws Exception
     * @throws \Exception
     */
    private static function savePDF(string $pdfContent): array
    {
        $dir = Yii::getAlias('@webroot') . '/upload/pdf';
        FileHelper::createDirectory($dir);
        $filename = Yii::$app->getSecurity()->generateRandomString(32);

        if (file_put_contents($dir . '/' . $filename . '.' . self::FILE_FORMAT, $pdfContent) === false) {
            throw new \Exception('Ошибка записи файла');
        }

        return [$dir, $filename, self::FILE_FORMAT];
    }
}
