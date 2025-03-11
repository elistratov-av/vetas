<?php

namespace app\modules\asur\controllers;

use app\common\components\asurService\ResponseTaskJob;
use yii\base\Controller;
use yii\helpers\FileHelper;
use yii\queue\db\Queue;
use yii\web\BadRequestHttpException;

class TaskController extends Controller
{
    public function actionIndex()
    {
        $xml = \Yii::$app->request->rawBody;
        if (empty($xml)) {
            throw new BadRequestHttpException();
        }

        if (YII_DEBUG) {
            $this->logResponse($xml);
        }

        /** @var Queue $queue */
        $queue = \Yii::$app->asur_queue;
        $queue->push(new ResponseTaskJob([
            'xml' => $xml
        ]));
    }

    private function logResponse($xml)
    {
        $path = FileHelper::normalizePath(\Yii::getAlias('@runtime/logs/asur'));
        if (FileHelper::createDirectory($path)) {
            file_put_contents($path . '/response_' . microtime(true) . '.xml', $xml, FILE_TEXT);
        }
    }
}
