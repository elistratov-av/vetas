<?php

namespace app\modules\v2\modules\payment\controllers;

use app\models\db\Visits;
use app\modules\soap\v2\queue\MosruStatusSender;
use app\modules\v2\modules\BaseController;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

class PaymentController extends BaseController
{
    // Типы статусов оплаты от ЕПШ
    const PAYMENT_REGISTERED = 'REGISTERED';
    const PAYMENT_IN_PROGRESS = 'IN_PROGRESS';
    const PAYMENT_PAID = 'PAID';
    const PAYMENT_NOT_PAID = 'NOT_PAID';

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
                        'payment-callback',
                    ],
                ],
            ]
        );
    }

    /**
     * Метод колбек для ЕПШ (Единый платёжный шлюз) для передачи статуса оплаты
     *
     * @param string      $requestUID
     * @param string      $status
     * @param string|null $uip
     * @param string|null $uin
     * @param string|null $receiptUrl отправляется только в случае авторизации по токену СУДИР (у нас basic auth, не наш случай)
     * @return true
     * @throws BadRequestHttpException
     */
    public function actionPaymentCallback(string $requestUID, string $status, string $uip = null, string $uin = null, string $receiptUrl = null)
    {
        if ($status === self::PAYMENT_PAID) {
            if (!$visit = Visits::findOne(['payment_request_uid' => $requestUID])) {
                throw new BadRequestHttpException("Не найден приём ожидающий оплаты по requestUID: $requestUID");
            }

            $visit->is_paid = true;
            if (!$visit->save()) {
                $errors = $visit->getErrorSummary(true);
                throw new BadRequestHttpException(empty($errors) ? 'Ошибка при попытке изменения статуса оплаты' : implode("\n", array_values($errors)));
            }

            (new MosruStatusSender())->visitPaid($visit->id, "{$_ENV['VKS_USER_URL']}/$visit->guid_video");
        }

        return true;
    }
}
