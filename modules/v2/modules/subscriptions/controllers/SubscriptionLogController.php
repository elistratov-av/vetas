<?php

namespace app\modules\v2\modules\subscriptions\controllers;

use app\models\db\Files;
use app\modules\v2\modules\BaseController;
use app\modules\v2\modules\files\models\FilesModel;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

class SubscriptionLogController extends BaseController
{
    /**
     * @return array
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                'http_authenticator' => [
                    'except' => [
                        'subscription-file-list-by-token',
                    ],
                ],
            ]
        );
    }

    /**
     * Список файлов привязанных к оповещению
     *
     * @param string $file_token
     * @return Files[]
     * @throws BadRequestHttpException
     */
    public function actionSubscriptionFileListByToken(string $file_token)
    {
        return [
            'result' => (new FilesModel())->getSubscriptionLogFilesByToken($file_token)
        ];
    }
}
