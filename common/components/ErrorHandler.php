<?php

namespace app\common\components;

use Yii;
use yii\web\ErrorHandler as EH;
use yii\web\Response;

class ErrorHandler extends EH {

    /**
     * Renders the exception.
     *
     * @param \Exception $exception the exception to be rendered.
     */
    protected function renderException($exception) {
        if (Yii::$app->has('response')) {
            $response = Yii::$app->getResponse();
            // reset parameters of response to avoid interference with partially created response data
            // in case the error occurred while sending the response.
            $response->isSent = FALSE;
            $response->stream = NULL;
            $response->data = NULL;
            $response->content = NULL;
        }
        else {
            $response = new Response();
        }

        $response->format = 'json';
        $response->setStatusCodeByException($exception);
        $data = $this->convertExceptionToArray($exception);

        try {
            $monitoring = Yii::$app->get('monitoring');
            if ($monitoring !== null) {
                $data = $monitoring->formatExceptionData($data);
            }
        } catch (\Throwable $e) {
            // do nothing
        }
        if ($_ENV['YII_DEBUG'] == 'true') {
            $errorMessage = $exception->getMessage();
            $errorFile = $exception->getFile();
            $errorLine = $exception->getLine();
            $data['message'] = "An error occured in $errorFile on line $errorLine: $errorMessage";
        }
        $response->data = $data;

        $response->send();
    }
}
