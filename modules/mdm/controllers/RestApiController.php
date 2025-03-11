<?php

namespace app\modules\mdm\controllers;

use modules\adminfstek\behaviors\ExternalServiceAuth;
use app\models\db\mdm\MdmLog;
use app\modules\adminfstek\traits\ExternalLogTrait;
use app\modules\mdm\models\SaveHandler;
use yii\filters\ContentNegotiator;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;

class RestApiController extends Controller
{
    use ExternalLogTrait;

    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        $behaviors = [];

        $httpAuth = ArrayHelper::getValue($this->module->params, 'auth');
        if (!empty($httpAuth)) {
            $behaviors['httpAuth'] = $httpAuth;
        }

        $authEnabled = ArrayHelper::getValue(\Yii::$app->params, 'external_services_auth_enabled');
        if ($authEnabled === true) {
            $behaviors['httpHeaderAuth'] = [
                'class' => ExternalServiceAuth::class,
                'pattern' => ArrayHelper::getValue($this->module->params, 'authToken'),
            ];
        }

        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::class,
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
                'application/vnd.api+json' => Response::FORMAT_JSON,
            ],
        ];

        return $behaviors;
    }

    /**
     * @param $action
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        $module = \Yii::$app->getModule('mdm');

        $enabled = ArrayHelper::getValue($module->params, 'enabled');
        if ($enabled !== true) {
            throw new BadRequestHttpException('Module mdm is disabled');
        }

        $enableLog = ArrayHelper::getValue($module->params, 'enableLog');
        if ($enableLog === true) {
            $request = \Yii::$app->request;
            if (\Yii::$app->request->isPost) {
                $log = new MdmLog([
                    'data' => $request->rawBody
                ]);
                $log->save(false);
            }
        }

        $result = parent::beforeAction($action);
        if ($result) {
            $this->logAuth();
        }

        return $result;
    }

    /**
     * @return array
     * @throws \Throwable
     */
    public function actionSave()
    {
        $data = json_decode(\Yii::$app->request->getRawBody());

        if (!is_object($data) && !is_array($data)) {
            return [
                'result' => 'Error',
                'error' => 400,
                'error_message' => 'Некорректный формат запроса',
            ];
        }

        if (!is_array($data)) {
            $data = [$data];
        }

        foreach ($data as $item) {
            try {
                $handler = new SaveHandler(['data' => $item]);
                $result = $handler->run();
                if (!empty($result['error'])) {
                    return $result;
                }
            } catch (\Throwable $e) {
                return [
                    'result' => 'Error',
                    'error' => 500,
                    'error_message' => $e->getMessage(),
                ];
            }
        }

        return [
            'result' => 'OK',
            'error' => 0,
        ];
    }
}
