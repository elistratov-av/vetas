<?

namespace app\commands\cron;

use app\common\models\VisitStatus;
use app\models\db\Visits;
use app\modules\soap\v2\queue\MosruStatusSender;
use DateTime;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

class TelevetController extends Controller
{


    public function getHelp()
    {
        return 'Контроллер для работы с телеветеранирей';
    }

    public function externalCancelUnpaid()
    {
        return 'Команда отмены неоплаченных приемов';
    }


    const PAYMENT_AWAITING_TIME_MINUTES = 30;

    /**
     * Отмена неоплаченных визитов по телеветеренарии
     * @return int|bool
     */
    public function actionCancelUnpaid()
    {
        $date = (new DateTime())->modify('-' . self::PAYMENT_AWAITING_TIME_MINUTES . ' minutes')->format('Y-m-d H:i:s');
        $visitsToCancel = Visits::find()
            ->where(['status' => VisitStatus::NEW])
            ->andWhere(['is_paid' => false])
            ->andWhere(['type' => Visits::TYPE_ONLINE])
            ->andWhere(['<=', 'payment_request_date', $date])
            ->all();
        $statusSender = new MosruStatusSender();
        foreach ($visitsToCancel as $visit) {
            $visit->status = VisitStatus::CANCELED;
            $visit->change_reason = 'Отменён автоматически по причине неуплаты своевременно';
            $visit->cancel_initiator = Visits::INITIATOR_IS_CLINIC;
            if (!$visit->save()) {
                Console::error("Не удалось обновить прием $visit->id");
                return ExitCode::SOFTWARE;
            }
            $statusSender->visitCancelPayment($visit->id);
        }
        Console::output(sizeof($visitsToCancel) . " приемов отмененно по неуплате");
        $statusSender = new MosruStatusSender();
        return ExitCode::OK;
    }
}
