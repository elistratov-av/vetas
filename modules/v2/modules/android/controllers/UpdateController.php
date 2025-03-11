<?php

namespace app\modules\v2\modules\android\controllers;

use app\models\db\AndroidBuild;
use app\modules\v2\modules\BaseController;
use yii\filters\ContentNegotiator;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class UpdateController extends BaseController
{
    public function behaviors(): array
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::class,
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                    'application/vnd.api+json' => Response::FORMAT_JSON,
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function actionLatest()
    {
        /* @var $model \app\models\db\AndroidBuild */
        $model = AndroidBuild::find()
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(1)
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException();
        }

        return [
            'result' =>
                [
                    "latestVersion" => $model->version,
                    "url" => Url::to(
                        '@web/android/' . $model->filename,
                        true
                    ),
                    "updateRequired" => $model->update_required,
                ]
        ];
    }
}
