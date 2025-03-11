<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 12.07.19
 * Time: 13:41
 */

namespace app\modules\admin\models\export;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use yii\web\Response;

trait SendXlsxTrait
{
    /**
     * @param Spreadsheet $spreadsheet
     * @param string $attachmentName
     * @param array $options
     * @return \yii\console\Response|Response
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \yii\web\RangeNotSatisfiableHttpException
     */
    protected function  sendXlsx(Spreadsheet $spreadsheet, string $attachmentName, $options = [])
    {
        $tmpResource = tmpfile();
        if ($tmpResource === false) {
            throw new \RuntimeException('Unable to create temporary file.');
        }

        $tmpResourceMetaData = stream_get_meta_data($tmpResource);
        $tmpFileName = $tmpResourceMetaData['uri'];

        $writer = new Xlsx($spreadsheet);
        $writer->save($tmpFileName);
        unset($writer);

        $tmpFileStatistics = fstat($tmpResource);
        if ($tmpFileStatistics['size'] > 0) {
            return Yii::$app->getResponse()->sendStreamAsFile($tmpResource, $attachmentName, $options);
        }

        // some writers, like 'Xlsx', may delete target file during the process, making temporary file resource invalid
        $response = Yii::$app->getResponse();
        $response->on(Response::EVENT_AFTER_SEND, function() use ($tmpResource) {
            // with temporary file resource closing file matching its URI will be deleted, even if resource is invalid
            fclose($tmpResource);
        });
        return $response->sendFile($tmpFileName, $attachmentName, $options);
    }
}
