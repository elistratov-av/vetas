<?php

namespace app\modules\elk\controllers;

use app\common\soap\SoapAction;
use app\common\soap\SoapException;
use app\models\db\elk\ElkLog;
use app\modules\adminfstek\traits\ExternalLogTrait;
use app\modules\elk\models\PetsHandler;
use modules\adminfstek\behaviors\ExternalServiceAuth;
use yii\base\Controller;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

class WsdlController extends Controller
{
    use ExternalLogTrait;

    public function behaviors()
    {
        $behaviors = [];

        $authEnabled = ArrayHelper::getValue(\Yii::$app->params, 'external_services_auth_enabled');
        if ($authEnabled === true) {
            $behaviors['httpHeaderAuth'] = [
                'class' => ExternalServiceAuth::class,
                'pattern' => ArrayHelper::getValue($this->module->params, 'authToken'),
            ];
        }

        $elk = \Yii::$app->getModule('elk');

        $behaviors['access'] = [
            'class' => AccessControl::class,
            'only' => ['index'],
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['?'],
                    'ips' => (!empty($elk->params['allowedIPs'])) ? $elk->params['allowedIPs'] : ['*'],
                ],
            ],
            'denyCallback' => function ($rule, $action) {
                $this->logAuth(false);
                throw new SoapException('You are not allowed to access this page');
            },
        ];

        return $behaviors;
    }

    public function beforeAction($action)
    {
        $module = \Yii::$app->getModule('elk');

        $enabled = ArrayHelper::getValue($module->params, 'enabled');
        if ($enabled !== true) {
            throw new BadRequestHttpException('Module elk is disabled');
        }

        if ($module->params['enableLog']) {
            $request = \Yii::$app->request;
            if (\Yii::$app->request->isPost) {
                $log = new ElkLog([
                    'xml' => $request->rawBody
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

    public function actions()
    {
        $elk = \Yii::$app->getModule('elk');
        return [
            'index' => [
                'class' => SoapAction::class,
                'serviceUrl' => (!empty($elk->params['soapServiceUrl'])) ? $elk->params['soapServiceUrl'] : null,
                'wsdlUrl' => (!empty($elk->params['wsdlUrl'])) ? $elk->params['wsdlUrl'] : null,
                'wsdlOptions' => [
                    'namespace' => 'soap-service',
                    'serviceName' => 'Pets'
                ]
            ],
        ];
    }

    /**
     * @soap
     * @param \app\modules\elk\types\Owner $Owner
     * @param \app\modules\elk\types\Animals $Animals
     * @return \app\modules\elk\types\Response
     * @throws \Throwable
     */
    public function save($Owner, $Animals)
    {
        return PetsHandler::save($Owner, $Animals);
    }

    /**
     * @soap
     * @param string $ID
     * @return \app\modules\elk\types\Response
     * @throws \Throwable
     */
    public function delete($ID)
    {
        return PetsHandler::delete($ID);
    }
}
