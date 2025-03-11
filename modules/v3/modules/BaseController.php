<?php

namespace app\modules\v3\modules;

use app\common\components\JwtHttpBearerAuth;
use yii\filters\ContentNegotiator;
use yii\filters\Cors;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;

class BaseController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return [
            'cors' => [
                'class' => Cors::class,
            ],
            'contentNegotiator' => [
                'class' => ContentNegotiator::class,
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                    'application/vnd.api+json' => Response::FORMAT_JSON,
                ],
            ],

        ];
    }

    public function beforeAction($action)
    {
        ini_set('memory_limit', -1);
        return parent::beforeAction($action);
    }
}
