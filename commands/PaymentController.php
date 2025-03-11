<?php

namespace app\commands;

use app\common\models\VisitStatus;
use app\models\db\Users;
use app\models\db\Visits;
use app\modules\soap\v2\queue\MosruStatusSender;
use yii\console\Controller;

class PaymentController extends Controller
{
    const PAYMENT_AWAITING_TIME_MINUTES = 15;

    public function actionCancelNonPaid()
    {
        $now = new \DateTime();
        $now->modify('-'.self::PAYMENT_AWAITING_TIME_MINUTES.' minutes');

        /** @var Visits[] $visitsToCancel */
        $visitsToCancel = Visits::find()
            ->where(['status' => VisitStatus::NEW])
            ->andWhere(['is_paid' => false])
            ->andWhere(['type' => Visits::TYPE_ONLINE])
            ->andWhere(['<', 'payment_request_date', $now->format('Y-m-d H:i:s')])
            ->all()
        ;

        $statusSender = (new MosruStatusSender);
        foreach($visitsToCancel as $visit)
        {
            $visit->status = VisitStatus::CANCELED;
            $visit->change_reason = 'Отменён автоматически по причине неуплаты своевременно';
            $visit->cancel_initiator = Visits::INITIATOR_IS_CLINIC;
            if (!$visit->save()) {
                var_dump($visit->errors); die;
            }
            $statusSender->visitCancelPaymentByExpirement($visit->id);
        }
    }
}
